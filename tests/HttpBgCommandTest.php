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
        $bgRequest->accept      = "something' & kill -9 1";

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

    public function testBuildCurlCommandGetDefault()
    {
        $bgRequest         = HttpBg::getRequest();
        $bgRequest->id     = 'HttpBgRequest_6877075681de6';
        $bgRequest->method = 'get';
        $bgRequest->url    = 'https://httpbin.org/get';

        $expectedCommand = "curl \
    --write-out 'HttpBgRequest_6877075681de6 10 30 %{time_connect} %{time_total} %{exitcode} %{response_code}' \
    --connect-timeout 10 \
    --max-time 30 \
    --output /dev/null \
    --silent \
    --location \
    --request 'GET' \
    'https://httpbin.org/get'";

        $actualCommand = HttpBgCommand::buildCurlCommand($bgRequest);
        $this->assertSame($expectedCommand, $actualCommand);
    }

    public function testBuildCurlCommandGetModified()
    {
        $bgRequest                      = HttpBg::getRequest();
        $bgRequest->id                  = 'HttpBgRequest_6877075681de6';
        $bgRequest->method              = 'get';
        $bgRequest->url                 = 'https://httpbin.org/get';
        $bgRequest->connectionTimeout   = 500;
        $bgRequest->totalRequestTimeout = 1000;
        $bgRequest->requestBody         = json_encode(['key' => "something' & kill -9 1"]);
        $bgRequest->contentType         = 'application/json';
        $bgRequest->accept              = 'application/vnd.api+json';

        $data = "{\"key\":\"something'\'' & kill -9 1\"}";
        $expectedCommand = "curl \
    --write-out 'HttpBgRequest_6877075681de6 500 1000 %{time_connect} %{time_total} %{exitcode} %{response_code}' \
    --connect-timeout 500 \
    --max-time 1000 \
    --output /dev/null \
    --silent \
    --location \
    --request 'GET' \
    --header 'Accept: application/vnd.api+json' \
    --header 'Content-Type: application/json' \
    --data '{$data}' \
    'https://httpbin.org/get'";

        $actualCommand = HttpBgCommand::buildCurlCommand($bgRequest);
        $this->assertSame($expectedCommand, $actualCommand);
    }

    public function testEscapeArguments()
    {
        $bgRequest              = HttpBg::getRequest();
        $bgRequest->method      = "something' & kill -9 1";
        $bgRequest->url         = "something' & kill -9 1";
        $bgRequest->requestBody = "something' & kill -9 1";
        $bgRequest->contentType = "something' & kill -9 1";
        $bgRequest->accept      = "something' & kill -9 1";

        $bgRequest = HttpBgCommand::escapeArguments($bgRequest);
        $this->assertSame($bgRequest->method, "something'\'' & kill -9 1");
        $this->assertSame($bgRequest->method, 'something\'\\\'\' & kill -9 1');
        $this->assertSame($bgRequest->url, "something'\'' & kill -9 1");
        $this->assertSame($bgRequest->url, 'something\'\\\'\' & kill -9 1');
        $this->assertSame($bgRequest->requestBody, "something'\'' & kill -9 1");
        $this->assertSame($bgRequest->requestBody, 'something\'\\\'\' & kill -9 1');
        $this->assertSame($bgRequest->contentType, "something'\'' & kill -9 1");
        $this->assertSame($bgRequest->contentType, 'something\'\\\'\' & kill -9 1');
        $this->assertSame($bgRequest->accept, "something'\'' & kill -9 1");
        $this->assertSame($bgRequest->accept, 'something\'\\\'\' & kill -9 1');
    }
}
