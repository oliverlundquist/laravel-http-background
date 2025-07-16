<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use OliverLundquist\HttpBackground\HttpBg as RealHttpBg;
use OliverLundquist\HttpBackground\HttpBgCallback;

final class HttpBgProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([HttpBgCallback::class]);
        }
        Http::macro('background', function () {
            return new RealHttpBg;
        });
    }
}
