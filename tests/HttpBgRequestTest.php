<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground\Tests;

use Illuminate\Support\Str;
use OliverLundquist\HttpBackground\HttpBgRequest;

class HttpBgRequestTest extends TestCase
{
    public function testNewFromArray()
    {
        $properties = [
            'id'                  => 'HttpBgRequest_687455025b769',
            'pid'                 => 3454,
            'method'              => 'GET',
            'url'                 => 'https://httpbin.org/get',
            'requestBody'         => 'some payload',
            'contentType'         => 'text/plain',
            'accept'              => 'text/plain',
            'tag'                 => 'webhook_id_222',
            'connectionTimeout'   => 111,
            'totalRequestTimeout' => 222,
            'attempts'            => 333,
            'maxAttempts'         => 444,
            'invalidProperty'     => 'testing',
        ];
        $request = HttpBgRequest::newFromArray($properties);

        // all properties
        $this->assertTrue($request instanceof HttpBgRequest);
        foreach ($properties as $key => $value) {
            $key === 'invalidProperty'
                ? $this->assertFalse(isset($request->{$key}))
                : $this->assertSame($request->{$key}, $value);
        }

        // no properties
        $request = HttpBgRequest::newFromArray([]);
        $this->assertTrue($request instanceof HttpBgRequest);
        foreach ($properties as $key => $value) {
            in_array($key, ['id', 'method', 'url', 'tag', 'invalidProperty'])
                ? $this->assertFalse(isset($request->{$key}))
                : $this->assertTrue(isset($request->{$key}));
        }
    }

    public function testHydrate()
    {
        $properties = [
            'id'                    => 'HttpBgRequest_687455025b769',
            'pid'                   => 3454,
            'method'                => 'GET',
            'url'                   => 'https://httpbin.org/get',
            'request_body'          => 'some payload',
            'content_type'          => 'text/plain',
            'accept'                => 'text/plain',
            'tag'                   => 'webhook_id_222',
            'connection_timeout'    => 111,
            'total_request_timeout' => 222,
            'attempts'              => 333,
            'max_attempts'          => 444,
            'invalid_property'      => 'testing',
        ];
        $request = HttpBgRequest::newFromArray($properties);

        // all properties
        $this->assertTrue($request instanceof HttpBgRequest);
        foreach ($properties as $key => $value) {
            $key = Str::camel($key);
            $key === 'invalidProperty'
                ? $this->assertFalse(isset($request->{$key}))
                : $this->assertSame($request->{$key}, $value);
        }

        // no properties
        $request = HttpBgRequest::newFromArray([]);
        $this->assertTrue($request instanceof HttpBgRequest);
        foreach ($properties as $key => $value) {
            $key = Str::camel($key);
            in_array($key, ['id', 'method', 'url', 'tag', 'invalidProperty'])
                ? $this->assertFalse(isset($request->{$key}))
                : $this->assertTrue(isset($request->{$key}));
        }
    }

    public function testValidateRequestSuccess()
    {
        $bgRequest              = HttpBgRequest::newFromArray([]);
        $bgRequest->url         = 'https://www.php.net/';
        $bgRequest->method      = 'get';
        $bgRequest->contentType = 'application/json';
        $bgRequest->requestBody = json_encode(['json' => 'payload']);
        $this->assertTrue($bgRequest->validateRequest());
        $this->assertSame($bgRequest->validateRequest(true), []);

        $bgRequest              = HttpBgRequest::newFromArray([]);
        $bgRequest->url         = '/some/path?php=awesome&so=is#laravel';
        $bgRequest->method      = 'GET';
        $bgRequest->contentType = 'application/json';
        $bgRequest->requestBody = json_encode(['json' => 'payload']);
        $this->assertTrue($bgRequest->validateRequest());
        $this->assertSame($bgRequest->validateRequest(true), []);
    }

    public function testValidateRequestFail()
    {
        $bgRequest              = HttpBgRequest::newFromArray([]);
        $bgRequest->url         = '';
        $bgRequest->method      = '';
        $bgRequest->requestBody = '';
        $this->assertFalse($bgRequest->validateRequest());
        $this->assertSame(count($bgRequest->validateRequest(true)), 5);

        $bgRequest              = HttpBgRequest::newFromArray([]);
        $bgRequest->url         = 'https//httpbinzzzz.yx/get';
        $bgRequest->method      = 'getz';
        $bgRequest->requestBody = 'plain-text payload';
        $this->assertFalse($bgRequest->validateRequest());
        $this->assertSame(count($bgRequest->validateRequest(true)), 6);

        $bgRequest              = HttpBgRequest::newFromArray([]);
        $bgRequest->url         = 'path/without/slash';
        $bgRequest->method      = 'PUT\'T';
        $bgRequest->requestBody = '<xml><hey/></xml>';
        $bgRequest->contentType = 'application/xml';
        $this->assertFalse($bgRequest->validateRequest());
        $this->assertSame(count($bgRequest->validateRequest(true)), 5);
    }

    public function testToArray()
    {
        // all properties
        $properties = [
            'id'                  => 'HttpBgRequest_687455025b769',
            'pid'                 => 3454,
            'method'              => 'GET',
            'url'                 => 'https://httpbin.org/get',
            'requestBody'         => 'some payload',
            'contentType'         => 'text/plain',
            'accept'              => 'text/plain',
            'tag'                 => 'webhook_id_222',
            'connectionTimeout'   => 111,
            'totalRequestTimeout' => 222,
            'attempts'            => 333,
            'maxAttempts'         => 444,
            'invalidProperty'     => 'testing',
        ];
        $request = HttpBgRequest::newFromArray($properties)->toArray();
        $results = [];
        foreach ($properties as $key => $value) {
            if ($key === 'invalidProperty') {
                continue;
            }
            $key = Str::snake($key);
            $results[$key] = $value;
        }
        $this->assertSame($request, $results);

        // no properties
        $request = HttpBgRequest::newFromArray([])->toArray();
        $results = [
            'pid'                   => 0,
            'request_body'          => '',
            'content_type'          => '',
            'accept'                => '',
            'connection_timeout'    => 10,
            'total_request_timeout' => 30,
            'attempts'              => 0,
            'max_attempts'          => 1,
        ];
        $this->assertSame($request, $results);
    }
}
