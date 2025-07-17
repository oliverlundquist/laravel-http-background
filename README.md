## Laravel HTTP Background

[![PHPUnit](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/phpunit.yml)
[![Coverage](https://raw.githubusercontent.com/oliverlundquist/laravel-http-background/refs/heads/image-data/coverage.svg)](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/coverage.yml)
[![PHPStan](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/phpstan.yml/badge.svg?branch=master)](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/phpstan.yml)
[![PHP-CS-Fixer](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/php-cs-fixer.yml/badge.svg?branch=master)](https://github.com/oliverlundquist/laravel-http-background/actions/workflows/php-cs-fixer.yml)

Moves HTTP Requests out from PHP to a forked process on the server executing the request through cURL and then piping the results back into an Laravel Artisan command that fires events about the status of the response of the request. This enables PHP to execute without waiting for responses for its HTTP requests, enabling one server instance to handle lots of outgoing HTTP requests without using much resources or blocking the execution of PHP.

I wrote a blog about this package where I go into more detail about the motivation behind it and alternative methods that I tried before deciding to make it. It's available [<a href="https://oliverlundquist.com/2025/07/17/performing-http-requests-in-background.html" target="_blank">here</a>].

### Usage

```php
// use it directly
HttpBg::get('http://httpbin.org');

// or though a macro registered on the Laravel HTTP Client
Http::background()->get('http://httpbin.org');
```

Events are fired to track the progress and result of the request.
```php
Event::listen(function (HttpBgRequestSending $event) {});
Event::listen(function (HttpBgRequestSent $event) {});
Event::listen(function (HttpBgRequestSuccess $event) {});
Event::listen(function (HttpBgRequestFailed $event) {});
Event::listen(function (HttpBgRequestTimeout $event) {});
Event::listen(function (HttpBgRequestComplete $event) {});
```

A simple implementation that handles retries and informs integration partners about failed or timed out webhooks.

```php
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // cache request before sending it to background
        Event::listen(function (HttpBgRequestSent $event) {
            $request = $event->request;
            Cache::put($request->id, $request->toArray());
        });

        // rebuild request object after it has completed
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

            // inform partner about webhook timeouts
            // Mail::to('integration@partner.com')
            //     ->send(new FailedWebHookMail($request->tag));
        });
    }
}
```


### Windows Users

PowerShell is currently not supported. However, adding support shouldn't be too difficult, since the cURL arguments are mostly the same - just replace -o /dev/null with -o NUL.

I'm not a PowerShell expert, but I believe something like (Start-Job { & command }).Id could be used to retrieve the job ID. You could then check the job status later using Get-Job -Id $id.

If you're interested in contributing PowerShell support, feel free to open a pull request!
