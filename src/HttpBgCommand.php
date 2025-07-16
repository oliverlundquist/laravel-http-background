<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground;

use OliverLundquist\HttpBackground\Events\HttpBgRequestSending;
use OliverLundquist\HttpBackground\Events\HttpBgRequestSent;

class HttpBgCommand
{
    public static function execute(HttpBgRequest $request, string $callbackCommand = 'http-background:request-callback'): int
    {
        $basePath    = static::getBasePath();
        $curlCommand = static::buildCurlCommand($request);

        $command = <<<COMMAND
            cd {$basePath};
            ({$curlCommand} | xargs php artisan {$callbackCommand}) > /dev/null 2>&1 & echo $!
        COMMAND;

        HttpBgRequestSending::dispatch($request);
        $request->pid      = intval(exec($command));
        $request->attempts = $request->attempts + 1;
        HttpBgRequestSent::dispatch($request);

        return $request->pid;
    }

    public static function escapeArguments(HttpBgRequest $request): HttpBgRequest
    {
        $properties = ['method', 'url', 'requestBody', 'contentType', 'accept'];
        foreach ($properties as $property) {
            $request->{$property} = rtrim(ltrim(escapeshellarg($request->{$property}), '\''), '\'');
        }
        return $request;
    }

    public static function getBasePath(): string
    {
        return env('GITHUB_ACTIONS') === true ? __DIR__ . '/../' : base_path();
    }

    public static function buildCurlCommand(HttpBgRequest $request): string
    {
        $request = tap(clone $request, function ($request) {
            $request         = static::escapeArguments($request);
            $request->method = strtoupper($request->method);
        });
        $writeOut = implode(' ', [
            strval($request->id),
            strval($request->connectionTimeout),
            strval($request->totalRequestTimeout),
            '%{time_connect}',
            '%{time_total}',
            '%{exitcode}',
            '%{response_code}'
        ]);
        $curlCommand = <<<CURL_COMMAND
        curl \
            --write-out '{$writeOut}' \
            --connect-timeout {$request->connectionTimeout} \
            --max-time {$request->totalRequestTimeout} \
            --output /dev/null \
            --silent \
            --location \
            --request '{$request->method}' \

        CURL_COMMAND;

        // set accept header
        if (strlen($request->accept) > 0) {
            $curlCommand .= <<<CURL_COMMAND
                --header 'Accept: {$request->accept}' \

            CURL_COMMAND;
        }

        // set content-type header
        if (strlen($request->contentType) > 0 && strlen($request->requestBody) > 0) {
            $curlCommand .= <<<CURL_COMMAND
                --header 'Content-Type: {$request->contentType}' \

            CURL_COMMAND;
        }

        // set request body
        if (strlen($request->requestBody) > 0) {
            $curlCommand .= <<<CURL_COMMAND
                --data '{$request->requestBody}' \

            CURL_COMMAND;
        }

        // set request url
        $curlCommand .= <<<CURL_COMMAND
            '{$request->url}'
        CURL_COMMAND;

        return $curlCommand;
    }
}
