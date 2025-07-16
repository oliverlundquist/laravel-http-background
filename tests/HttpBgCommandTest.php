<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground\Tests;

use Illuminate\Support\Facades\Http;
use OliverLundquist\HttpBackground\Facades\HttpBg;
use OliverLundquist\HttpBackground\HttpBgCommand;

class HttpBgCommandTest extends TestCase
{
    public function testCommandEscapeArguments()
    {
        $bgRequest              = HttpBg::getRequest();
        $bgRequest->method      = "something' & kill -9 1";
        $bgRequest->url         = "something' & kill -9 1";
        $bgRequest->requestBody = "something' & kill -9 1";
        $bgRequest->contentType = "something' & kill -9 1";

        $bgRequest->pid = HttpBgCommand::execute($bgRequest);
        $request        = HttpBg::setRequest($bgRequest);
        $this->assertFiredEvents($request, [
            'HttpBgRequestSending',
            'HttpBgRequestSent',
            'HttpBgRequestComplete',
            'HttpBgRequestFailed'
        ]);
        $this->assertTrue($this->processHasExited($request));

        $bgRequest->pid = HttpBgCommand::execute($bgRequest);
        $request        = Http::background()->setRequest($bgRequest);
        $this->assertFiredEvents($request, [
            'HttpBgRequestSending',
            'HttpBgRequestSent',
            'HttpBgRequestComplete',
            'HttpBgRequestFailed'
        ]);
        $this->assertTrue($this->processHasExited($request));
    }

    public function testEscapeArguments()
    {
        $bgRequest              = HttpBg::getRequest();
        $bgRequest->method      = "something' & kill -9 1";
        $bgRequest->url         = "something' & kill -9 1";
        $bgRequest->requestBody = "something' & kill -9 1";
        $bgRequest->contentType = "something' & kill -9 1";

        $bgRequest = HttpBgCommand::escapeArguments($bgRequest);
        $this->assertSame($bgRequest->method, "something'\'' & kill -9 1");
        $this->assertSame($bgRequest->method, 'something\'\\\'\' & kill -9 1');
        $this->assertSame($bgRequest->url, "something'\'' & kill -9 1");
        $this->assertSame($bgRequest->url, 'something\'\\\'\' & kill -9 1');
        $this->assertSame($bgRequest->requestBody, "something'\'' & kill -9 1");
        $this->assertSame($bgRequest->requestBody, 'something\'\\\'\' & kill -9 1');
        $this->assertSame($bgRequest->contentType, "something'\'' & kill -9 1");
        $this->assertSame($bgRequest->contentType, 'something\'\\\'\' & kill -9 1');
    }
}
