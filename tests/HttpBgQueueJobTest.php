<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground\Tests;

use OliverLundquist\HttpBackground\Facades\HttpBg;
use OliverLundquist\HttpBackground\HttpBgQueueJob;

class HttpBgQueueJobTest extends TestCase
{
    public function testCommandDispatch()
    {
        $bgRequest = HttpBg::getRequest();
        $bgRequest->method = 'get';
        $bgRequest->url    = 'https://httpbin.org/get';
        $request           = HttpBg::setRequest($bgRequest);

        HttpBgQueueJob::dispatch($bgRequest);

        $this->assertFiredEvents($request, [
            'HttpBgRequestSending',
            'HttpBgRequestSent',
            'HttpBgRequestComplete',
            'HttpBgRequestSuccess'
        ]);
        $this->assertTrue($this->processHasExited($request));
    }
}
