<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground;

use OliverLundquist\HttpBackground\Events\HttpBgRequestSending;
use OliverLundquist\HttpBackground\Events\HttpBgRequestSent;

class HttpBgCommand
{
    public static function execute(HttpBgRequest $request, string $callbackCommand = 'http-background:request-callback'): int
    {
        $basePath = static::getBasePath();
        $request  = tap(clone $request, function ($request) {
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

        // cURL command
        $curlCommand = <<<CURL_COMMAND
            curl \
                --write-out '$writeOut' \
                --connect-timeout {$request->connectionTimeout} \
                --max-time {$request->totalRequestTimeout} \
                --output /dev/null \
                --silent \
                --location \
                --header 'Content-Type: {$request->contentType}' \
                --header 'Accept: {$request->contentType}' \
                --request '{$request->method}' \
                --data '{$request->requestBody}' \
                '{$request->url}'
        CURL_COMMAND;


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
        $properties = ['method', 'url', 'requestBody', 'contentType'];
        foreach ($properties as $property) {
            $request->{$property} = rtrim(ltrim(escapeshellarg($request->{$property}), '\''), '\'');
        }
        return $request;
    }

    protected static function getBasePath(): string
    {
        $basePath = base_path();
        if (strpos($basePath, 'vendor') !== false && strpos($basePath, 'testbench') !== false) {
            $basePath = __DIR__ . '/../';
        }
        return $basePath;
    }
}
