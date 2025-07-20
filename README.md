## Laravel HTTP Background

[![PHPUnit](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/phpunit.yml)
[![Coverage](https://raw.githubusercontent.com/oliverlundquist/laravel-http-background/refs/heads/image-data/coverage.svg)](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/coverage.yml)
[![PHPStan](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/phpstan.yml/badge.svg?branch=master)](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/phpstan.yml)
[![PHP-CS-Fixer](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/php-cs-fixer.yml/badge.svg?branch=master)](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/php-cs-fixer.yml)

Moves HTTP Requests out from PHP to a forked process on the server executing the request through cURL and then piping the results back into a Laravel Artisan command that fires events about the status of the response of the request. This enables PHP to execute without having to wait for responses from its HTTP requests, enabling one server instance to handle lots of outgoing HTTP requests without using many resources or blocking the execution of PHP. In my tests, 10 concurrent requests had a peak memory usage of only 40mb.

I wrote a blog about this package where I go into more detail about the motivation behind it and alternative methods that I tried before going down the route of forking HTTP requests into separate processes. It's available [<a href="https://oliverlundquist.com/2025/07/20/performing-http-requests-in-background.html" target="_blank">here</a>].

### Usage

```php
// use it directly
HttpBg::get('https://httpbin.org/get');
HttpBg::head('https://httpbin.org/head');
HttpBg::post('https://httpbin.org/post', json_encode(['json' => 'payload']));
HttpBg::patch('https://httpbin.org/patch', json_encode(['json' => 'payload']));
HttpBg::put('https://httpbin.org/put', json_encode(['json' => 'payload']));
HttpBg::delete('https://httpbin.org/delete');

// or though a macro registered on the Laravel HTTP Client
Http::background()->get('https://httpbin.org/get');
Http::background()->head('https://httpbin.org/head');
Http::background()->post('https://httpbin.org/post', json_encode(['json' => 'payload']));
Http::background()->patch('https://httpbin.org/patch', json_encode(['json' => 'payload']));
Http::background()->put('https://httpbin.org/put', json_encode(['json' => 'payload']));
Http::background()->delete('https://httpbin.org/delete');
```

### Options

#### ->connectionTimeout()
Timeout to establish a connection (in seconds).
```php
HttpBg::connectTimeout(10);
Http::background()->connectTimeout(10);
```

#### ->timeout()
Maximum time allowed for the whole request (in seconds).
```php
HttpBg::timeout(30);
Http::background()->timeout(30);
```

#### ->retry()
Max attempts that this request can be retried, see the implementation example below.
```php
HttpBg::retry(3);
Http::background()->retry(3);
```

#### ->setRequestTag()
Arbitrary data used to identify the request.
```php
HttpBg::setRequestTag('webhook callback for id: 31');
Http::background()->setRequestTag('webhook callback for id: 31');
```

#### ->contentType()
Set the Content-Type header.
```php
HttpBg::contentType('application/json');
Http::background()->contentType('application/json');
```

#### ->accept()
Set the Accept header.
```php
HttpBg::accept('application/json');
Http::background()->accept('application/json');
```

#### ->withBody()
Set the body of the request, this can also be set implicitly by calling any of the HTTP verb short-hand methods `->post()`, `->patch()`, `->put()`, `->delete()`. The withBody() method can also set the Content-Type and Accept headers by providing values for the second and third arguments.
```php
$contentType = 'application/json';
$accept      = 'application/json';
HttpBg::withBody(json_encode(['json' => 'payload']), $contentType, $accept);
Http::background()->withBody(json_encode(['json' => 'payload']), $contentType, $accept);
```

#### ->queue()
Push the request to the Laravel Queue and fork a process on your queue worker server instance instead of your main PHP application server. This is useful if you don't want forked processes on your application server but rather have them processed on your server instance running the queue worker.
```php
HttpBg::queue()->get('https://httpbin.org/get');
Http::background()->queue()->get('https://httpbin.org/get');
```

#### ->processIsRunning()
Check whether the background process (pid) is still running.
```php
$request = HttpBg::get('https://httpbin.org/get');
$request = Http::background()->get('https://httpbin.org/get');

$request->processIsRunning();
```

### Events

Events are fired to track the progress and result of the request.

```php
Event::listen(function (HttpBgRequestSending $event)  { Log::info($event->request); });
Event::listen(function (HttpBgRequestSent $event)     { Log::info($event->request); });
Event::listen(function (HttpBgRequestSuccess $event)  { Log::info($event->requestId); });
Event::listen(function (HttpBgRequestFailed $event)   { Log::info($event->requestId); });
Event::listen(function (HttpBgRequestTimeout $event)  { Log::info($event->requestId); });
Event::listen(function (HttpBgRequestComplete $event) { Log::info($event->requestId); });
```

### Implementation Example

This is an example of a basic implementation that handles request retries and sends notifications of failed and timed-out requests by mail.

```php
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(function (HttpBgRequestSent $event) {
            $request = $event->request;
            Cache::put($request->id, $request->toArray());
        });

        Event::listen(function (HttpBgRequestFailed|HttpBgRequestTimeout $event) {
            $requestId = $event->requestId;
            $request   = HttpBgRequest::newFromArray(Cache::get($requestId, []));

            if (! $request->validateRequest()) {
                return;
            }
            if ($request->maxAttempts > $request->attempts) {
                HttpBg::send($request);
                return;
            }
            Cache::forget($requestId);
            Mail::to('integration@partner.com')
                ->send(new FailedWebHookMail($request->tag));
        });
    }
}
```

### Windows Users

PowerShell is currently not supported. However, adding support shouldn't be too difficult, since the cURL arguments are mostly the same - just replace -o /dev/null with -o NUL.

I'm not a PowerShell expert, but I believe something like (Start-Job { & command }).Id could be used to retrieve the job ID. You could then check the job status later using Get-Job -Id $id.

If you're interested in contributing PowerShell support, feel free to open a pull request!
