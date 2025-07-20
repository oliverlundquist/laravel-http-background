<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground\Tests;

use Illuminate\Support\Facades\Event;
use OliverLundquist\HttpBackground\Events\HttpBgRequestComplete;
use OliverLundquist\HttpBackground\Events\HttpBgRequestFailed;
use OliverLundquist\HttpBackground\Events\HttpBgRequestSuccess;
use OliverLundquist\HttpBackground\Events\HttpBgRequestTimeout;

class HttpBgCallbackTest extends TestCase
{
    public function testRequestSuccessEventFired()
    {
        Event::fake();

        $arguments = [
            'request_id'            => 'HttpBgRequest_687455025a763',
            'connection_timeout'    => '10',
            'total_request_timeout' => '30',
            'curl_time_connect'     => '0.312264',
            'curl_time_total'       => '0.882319',
            'curl_exitcode'         => '0',
            'curl_response_code'    => '200',
        ];
        $this->artisan('http-background:request-callback', $arguments);

        Event::assertDispatchedTimes(HttpBgRequestSuccess::class, 1);
        Event::assertNotDispatched(HttpBgRequestTimeout::class);
        Event::assertNotDispatched(HttpBgRequestFailed::class);
        Event::assertDispatchedTimes(HttpBgRequestComplete::class, 1);
    }

    public function testRequestFailedEventFired()
    {
        Event::fake();

        $arguments = [
            'request_id'            => 'HttpBgRequest_687455025b764',
            'connection_timeout'    => '10',
            'total_request_timeout' => '30',
            'curl_time_connect'     => '0.000000',
            'curl_time_total'       => '0.006547',
            'curl_exitcode'         => '0',
            'curl_response_code'    => '000',
        ];
        $this->artisan('http-background:request-callback', $arguments);

        Event::assertNotDispatched(HttpBgRequestSuccess::class);
        Event::assertNotDispatched(HttpBgRequestTimeout::class);
        Event::assertDispatchedTimes(HttpBgRequestFailed::class, 1);
        Event::assertDispatchedTimes(HttpBgRequestComplete::class, 1);
    }

    public function testRequestFailedEventFiredWithResponseCodeLessThan200()
    {
        Event::fake();

        $arguments = [
            'request_id'            => 'HttpBgRequest_687455025b764',
            'connection_timeout'    => '10',
            'total_request_timeout' => '30',
            'curl_time_connect'     => '0.000000',
            'curl_time_total'       => '0.006547',
            'curl_exitcode'         => '0',
            'curl_response_code'    => '199',
        ];
        $this->artisan('http-background:request-callback', $arguments);

        Event::assertNotDispatched(HttpBgRequestSuccess::class);
        Event::assertNotDispatched(HttpBgRequestTimeout::class);
        Event::assertDispatchedTimes(HttpBgRequestFailed::class, 1);
        Event::assertDispatchedTimes(HttpBgRequestComplete::class, 1);
    }

    public function testRequestFailedEventFiredWithResponseCodeGreaterThan299()
    {
        Event::fake();

        $arguments = [
            'request_id'            => 'HttpBgRequest_687455025b764',
            'connection_timeout'    => '10',
            'total_request_timeout' => '30',
            'curl_time_connect'     => '0.000000',
            'curl_time_total'       => '0.006547',
            'curl_exitcode'         => '0',
            'curl_response_code'    => '300',
        ];
        $this->artisan('http-background:request-callback', $arguments);

        Event::assertNotDispatched(HttpBgRequestSuccess::class);
        Event::assertNotDispatched(HttpBgRequestTimeout::class);
        Event::assertDispatchedTimes(HttpBgRequestFailed::class, 1);
        Event::assertDispatchedTimes(HttpBgRequestComplete::class, 1);
    }

    public function testRequestFailedEventFiredWithNonZeroCurlExitCode()
    {
        Event::fake();

        $arguments = [
            'request_id'            => 'HttpBgRequest_687455025a763',
            'connection_timeout'    => '10',
            'total_request_timeout' => '30',
            'curl_time_connect'     => '0.312264',
            'curl_time_total'       => '0.882319',
            'curl_exitcode'         => '999',
            'curl_response_code'    => '200',
        ];
        $this->artisan('http-background:request-callback', $arguments);

        Event::assertNotDispatched(HttpBgRequestSuccess::class);
        Event::assertNotDispatched(HttpBgRequestTimeout::class);
        Event::assertDispatchedTimes(HttpBgRequestFailed::class, 1);
        Event::assertDispatchedTimes(HttpBgRequestComplete::class, 1);
    }

    public function testRequestTimeoutEventFired()
    {
        Event::fake();

        $arguments = [
            'request_id'            => 'HttpBgRequest_687455225c765',
            'connection_timeout'    => '10',
            'total_request_timeout' => '30',
            'curl_time_connect'     => '0.000000',
            'curl_time_total'       => '10.009058',
            'curl_exitcode'         => '28',
            'curl_response_code'    => '000',
        ];
        $this->artisan('http-background:request-callback', $arguments);

        Event::assertNotDispatched(HttpBgRequestSuccess::class);
        Event::assertDispatchedTimes(HttpBgRequestTimeout::class, 1);
        Event::assertNotDispatched(HttpBgRequestFailed::class, 1);
        Event::assertDispatchedTimes(HttpBgRequestComplete::class, 1);
    }

    public function testRequestTimeoutEventFiredConnectionTimeoutReached()
    {
        Event::fake();

        $arguments = [
            'request_id'            => 'HttpBgRequest_687455225c765',
            'connection_timeout'    => '10',
            'total_request_timeout' => '30',
            'curl_time_connect'     => '10.009058',
            'curl_time_total'       => '10.009058',
            'curl_exitcode'         => '999',
            'curl_response_code'    => '000',
        ];
        $this->artisan('http-background:request-callback', $arguments);

        Event::assertNotDispatched(HttpBgRequestSuccess::class);
        Event::assertDispatchedTimes(HttpBgRequestTimeout::class, 1);
        Event::assertNotDispatched(HttpBgRequestFailed::class, 1);
        Event::assertDispatchedTimes(HttpBgRequestComplete::class, 1);
    }

    public function testRequestTimeoutEventFiredTotalTimeoutReached()
    {
        Event::fake();

        $arguments = [
            'request_id'            => 'HttpBgRequest_687455225c765',
            'connection_timeout'    => '10',
            'total_request_timeout' => '30',
            'curl_time_connect'     => '10.000000',
            'curl_time_total'       => '30.009058',
            'curl_exitcode'         => '999',
            'curl_response_code'    => '000',
        ];
        $this->artisan('http-background:request-callback', $arguments);

        Event::assertNotDispatched(HttpBgRequestSuccess::class);
        Event::assertDispatchedTimes(HttpBgRequestTimeout::class, 1);
        Event::assertNotDispatched(HttpBgRequestFailed::class, 1);
        Event::assertDispatchedTimes(HttpBgRequestComplete::class, 1);
    }
}
