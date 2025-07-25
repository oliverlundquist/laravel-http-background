<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground\Tests;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use OliverLundquist\HttpBackground\Facades\HttpBg;
use OliverLundquist\HttpBackground\HttpBgQueueJob;
use OliverLundquist\HttpBackground\HttpBgRequest;

class HttpBgTest extends TestCase
{
    public function testGet()
    {
        $request = HttpBg::get('https://httpbin.org/get');
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));

        $request = Http::background()->get('https://httpbin.org/get');
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));
    }

    public function testGetWithParameters()
    {
        $request = HttpBg::get('https://httpbin.org/get?with=someparams');
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));

        $request = Http::background()->get('https://httpbin.org/get?with=someparams');
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));
    }

    public function testHead()
    {
        $request = HttpBg::head('https://httpbin.org/head');
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));

        $request = Http::background()->head('https://httpbin.org/head');
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));
    }

    public function testPost()
    {
        $request = HttpBg::post('https://httpbin.org/post');
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));

        $request = Http::background()->post('https://httpbin.org/post');
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));
    }

    public function testPostWithBody()
    {
        $request = HttpBg::post('https://httpbin.org/post', json_encode(['request' => 'body']));
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));

        $request = Http::background()->post('https://httpbin.org/post', json_encode(['request' => 'body']));
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));
    }

    public function testPatch()
    {
        $request = HttpBg::patch('https://httpbin.org/patch');
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));

        $request = Http::background()->patch('https://httpbin.org/patch');
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));
    }

    public function testPatchWithBody()
    {
        $request = HttpBg::patch('https://httpbin.org/patch', json_encode(['request' => 'body']));
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));

        $request = Http::background()->patch('https://httpbin.org/patch', json_encode(['request' => 'body']));
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));
    }

    public function testPut()
    {
        $request = HttpBg::put('https://httpbin.org/put');
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));

        $request = Http::background()->put('https://httpbin.org/put');
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));
    }

    public function testPutWithBody()
    {
        $request = HttpBg::put('https://httpbin.org/put', json_encode(['request' => 'body']));
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));

        $request = Http::background()->put('https://httpbin.org/put', json_encode(['request' => 'body']));
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));
    }

    public function testDelete()
    {
        $request = HttpBg::delete('https://httpbin.org/delete');
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));

        $request = Http::background()->delete('https://httpbin.org/delete');
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));
    }

    public function testDeleteWithBody()
    {
        $request = HttpBg::delete('https://httpbin.org/delete', json_encode(['request' => 'body']));
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));

        $request = Http::background()->delete('https://httpbin.org/delete', json_encode(['request' => 'body']));
        $this->assertTrue($request->processIsRunning());
        $this->assertTrue($this->processHasExited($request));
    }

    public function testSend()
    {
        $bgRequest         = HttpBg::getRequest();
        $bgRequest->method = 'get';
        $bgRequest->url    = 'https://httpbin.org/get';

        $request = HttpBg::send($bgRequest);
        $this->assertTrue($request->processIsRunning());
        $this->assertSame($request->getRequest()->method, 'get');
        $this->assertSame($request->getRequest()->url, 'https://httpbin.org/get');
        $this->assertTrue($this->processHasExited($request));

        $request = Http::background()->send($bgRequest);
        $this->assertTrue($request->processIsRunning());
        $this->assertSame($request->getRequest()->method, 'get');
        $this->assertSame($request->getRequest()->url, 'https://httpbin.org/get');
        $this->assertTrue($this->processHasExited($request));
    }

    public function testSuccessfulRequestEvents()
    {
        $request = HttpBg::get('https://httpbin.org/get');

        $this->assertFiredEvents($request, [
            'HttpBgRequestSending',
            'HttpBgRequestSent',
            'HttpBgRequestComplete',
            'HttpBgRequestSuccess'
        ]);
        $this->assertTrue($this->processHasExited($request));
    }

    public function testFailedRequestEvents()
    {
        $request = HttpBg::get('https://httpbinzzzz.yx/get');

        $this->assertFiredEvents($request, [
            'HttpBgRequestSending',
            'HttpBgRequestSent',
            'HttpBgRequestComplete',
            'HttpBgRequestFailed'
        ]);
        $this->assertTrue($this->processHasExited($request));
    }

    public function testFailedRequestEventWithHttpCodeLessThan200()
    {
        $request = HttpBg::get('https://httpbin.org/status/199');

        $this->assertFiredEvents($request, [
            'HttpBgRequestSending',
            'HttpBgRequestSent',
            'HttpBgRequestComplete',
            'HttpBgRequestFailed'
        ]);
        $this->assertTrue($this->processHasExited($request));
    }

    public function testFailedRequestEventWithHttpCodeGreaterThan299()
    {
        $request = HttpBg::get('https://httpbin.org/status/300');

        $this->assertFiredEvents($request, [
            'HttpBgRequestSending',
            'HttpBgRequestSent',
            'HttpBgRequestComplete',
            'HttpBgRequestFailed'
        ]);
        $this->assertTrue($this->processHasExited($request));
    }

    public function testTimedOutRequestEvents()
    {
        $request = HttpBg::connectTimeout(1)->timeout(2)->get('http://10.255.255.1/get');

        $this->assertFiredEvents($request, [
            'HttpBgRequestSending',
            'HttpBgRequestSent',
            'HttpBgRequestComplete',
            'HttpBgRequestTimeout'
        ]);
        $this->assertTrue($this->processHasExited($request));
    }

    public function testQueue()
    {
        Queue::fake();

        HttpBg::queue()->get('https://httpbin.org/get');
        Http::background()->queue()->get('https://httpbin.org/get');

        Queue::assertPushed(HttpBgQueueJob::class, 2);
    }

    public function testQueueWithoutMocking()
    {
        $request = HttpBg::queue()->get('https://httpbin.org/get');
        $this->assertFiredEvents($request, [
            'HttpBgRequestSending',
            'HttpBgRequestSent',
            'HttpBgRequestComplete',
            'HttpBgRequestSuccess'
        ]);
        $this->assertTrue($this->processHasExited($request));

        $request = Http::background()->queue()->get('https://httpbin.org/get');
        $this->assertFiredEvents($request, [
            'HttpBgRequestSending',
            'HttpBgRequestSent',
            'HttpBgRequestComplete',
            'HttpBgRequestSuccess'
        ]);
        $this->assertTrue($this->processHasExited($request));
    }

    public function testWithBody()
    {
        $body    = json_encode(['key' => 'value']);
        $request = HttpBg::withBody($body)->getRequest();
        $this->assertSame($request->requestBody, $body);
        $this->assertSame($request->contentType, 'application/json');
        $this->assertSame($request->accept, 'application/json');

        $body        = json_encode(['key' => 'value']);
        $contentType = 'application/vnd.api+json';
        $accept      = 'application/vnd.api+json';
        $request     = HttpBg::withBody($body, $contentType, $accept)->getRequest();
        $this->assertSame($request->requestBody, $body);
        $this->assertSame($request->contentType, $contentType);
        $this->assertSame($request->accept, $accept);

        $body    = json_encode(['key' => 'value']);
        $request = Http::background()->withBody($body)->getRequest();
        $this->assertSame($request->requestBody, $body);
        $this->assertSame($request->contentType, 'application/json');
        $this->assertSame($request->accept, 'application/json');

        $body        = json_encode(['key' => 'value']);
        $contentType = 'application/vnd.api+json';
        $accept      = 'application/vnd.api+json';
        $request     = Http::background()->withBody($body, $contentType, $accept)->getRequest();
        $this->assertSame($request->requestBody, $body);
        $this->assertSame($request->contentType, $contentType);
        $this->assertSame($request->accept, $accept);
    }

    public function testContentType()
    {
        $contentType = 'application/vnd.api+json';
        $request     = HttpBg::contentType($contentType)->getRequest();
        $this->assertSame($request->contentType, $contentType);

        $contentType = 'application/vnd.api+json';
        $request     = Http::background()->contentType($contentType)->getRequest();
        $this->assertSame($request->contentType, $contentType);
    }

    public function testAccept()
    {
        $accept  = 'application/vnd.api+json';
        $request = HttpBg::accept($accept)->getRequest();
        $this->assertSame($request->accept, $accept);

        $accept  = 'application/vnd.api+json';
        $request = Http::background()->accept($accept)->getRequest();
        $this->assertSame($request->accept, $accept);
    }

    public function testConnectTimeout()
    {
        $connectTimeout = 1000;
        $request        = HttpBg::connectTimeout($connectTimeout)->getRequest();
        $this->assertSame($request->connectionTimeout, $connectTimeout);

        $connectTimeout = 1000;
        $request        = Http::background()->connectTimeout($connectTimeout)->getRequest();
        $this->assertSame($request->connectionTimeout, $connectTimeout);
    }

    public function testTimeout()
    {
        $timeout = 1000;
        $request = HttpBg::timeout($timeout)->getRequest();
        $this->assertSame($request->totalRequestTimeout, $timeout);

        $timeout = 1000;
        $request = Http::background()->timeout($timeout)->getRequest();
        $this->assertSame($request->totalRequestTimeout, $timeout);
    }

    public function testRetry()
    {
        $retry   = 5;
        $request = HttpBg::retry($retry)->getRequest();
        $this->assertSame($request->maxAttempts, $retry);

        $retry   = 5;
        $request = Http::background()->retry($retry)->getRequest();
        $this->assertSame($request->maxAttempts, $retry);
    }

    public function testTag()
    {
        $tag     = 'webhook_id_5';
        $request = HttpBg::setRequestTag($tag)->getRequest();
        $this->assertSame($request->tag, $tag);

        $tag     = 'webhook_id_5';
        $request = Http::background()->setRequestTag($tag)->getRequest();
        $this->assertSame($request->tag, $tag);
    }

    public function testGetRequest()
    {
        $this->assertTrue(HttpBg::getRequest() instanceof HttpBgRequest);
        $this->assertTrue(Http::background()->getRequest() instanceof HttpBgRequest);
    }

    public function testSetRequest()
    {
        $method     = 'post';
        $url        = 'https://somewhere.com';
        $newRequest = HttpBgRequest::newFromArray(compact('method', 'url'));

        $request = HttpBg::setRequest($newRequest)->getRequest();
        $this->assertSame($request->method, $method);
        $this->assertSame($request->url, $url);

        $request = Http::background()->setRequest($newRequest)->getRequest();
        $this->assertSame($request->method, $method);
        $this->assertSame($request->url, $url);
    }

    public function testProcessIsRunningWithPidZero()
    {
        $request = HttpBg::getRequest();
        $request->pid = 0;
        $this->assertFalse(HttpBg::setRequest($request)->processIsRunning());
    }

    public function testSendRequestWithInvalidRequest()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Request Method Not Set | Invalid Method Set | Request URL Not Set | Invalid URL (Laravel Validation) | Invalid URL (filter_var Validation)');

        $request = Http::background()->getRequest();
        HttpBg::send($request);
    }
}
