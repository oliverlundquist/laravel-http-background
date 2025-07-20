<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground;

use Illuminate\Console\Command;
use OliverLundquist\HttpBackground\Events\HttpBgRequestComplete;
use OliverLundquist\HttpBackground\Events\HttpBgRequestFailed;
use OliverLundquist\HttpBackground\Events\HttpBgRequestSuccess;
use OliverLundquist\HttpBackground\Events\HttpBgRequestTimeout;

class HttpBgCallback extends Command
{
    protected $signature = '
        http-background:request-callback
            {request_id}
            {connection_timeout}
            {total_request_timeout}
            {curl_time_connect}
            {curl_time_total}
            {curl_exitcode}
            {curl_response_code}
    ';
    protected $description = 'Process output from background cURL request.';

    public function handle(): void
    {
        $curlErrorCode = intval($this->argument('curl_exitcode'));
        /** @var int|string $requestId */
        $requestId     = $this->argument('request_id');
        $requestId     = is_numeric($requestId) ? intval($requestId) : strval($requestId);

        // successful
        if ($this->requestSuccessful($curlErrorCode)) {
            HttpBgRequestSuccess::dispatch($requestId);
        }
        // timed out
        if ($this->requestTimedOut($curlErrorCode)) {
            HttpBgRequestTimeout::dispatch($requestId);
        }
        // failed
        if (! $this->requestSuccessful($curlErrorCode) && ! $this->requestTimedOut($curlErrorCode)) {
            HttpBgRequestFailed::dispatch($requestId);
        }
        // complete
        HttpBgRequestComplete::dispatch($requestId);
    }

    protected function requestSuccessful(int $curlErrorCode): bool
    {
        $curlResponseCode = intval($this->argument('curl_response_code'));

        if ($curlErrorCode !== 0) {
            return false;
        }
        if ($curlResponseCode === 0) {
            return false;
        }
        if ($curlResponseCode < 200) {
            return false;
        }
        if ($curlResponseCode > 299) {
            return false;
        }
        return true;
    }

    protected function requestTimedOut(int $curlErrorCode): bool
    {
        $connectionTimeout   = intval($this->argument('connection_timeout'));
        $totalRequestTimeout = intval($this->argument('total_request_timeout'));
        $curlTimeConnect     = floatval($this->argument('curl_time_connect'));
        $curlTimeTotal       = floatval($this->argument('curl_time_total'));

        if ($curlErrorCode === 28) {
            return true;
        }
        if ($connectionTimeout < $curlTimeConnect) {
            return true;
        }
        if ($totalRequestTimeout < $curlTimeTotal) {
            return true;
        }
        return false;
    }
}
