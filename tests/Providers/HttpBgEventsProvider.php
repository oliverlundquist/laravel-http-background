<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground\Tests\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use OliverLundquist\HttpBackground\Events\HttpBgRequestComplete;
use OliverLundquist\HttpBackground\Events\HttpBgRequestFailed;
use OliverLundquist\HttpBackground\Events\HttpBgRequestSending;
use OliverLundquist\HttpBackground\Events\HttpBgRequestSent;
use OliverLundquist\HttpBackground\Events\HttpBgRequestSuccess;
use OliverLundquist\HttpBackground\Events\HttpBgRequestTimeout;

final class HttpBgEventsProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(function (HttpBgRequestSending $event) {
            $filePath = __DIR__ . '/../Storage/' . $event->request->id . '_events.txt';
            file_put_contents($filePath, 'HttpBgRequestSending' . PHP_EOL, FILE_APPEND);
        });

        Event::listen(function (HttpBgRequestSent $event) {
            $filePath = __DIR__ . '/../Storage/' . $event->request->id . '_events.txt';
            file_put_contents($filePath, 'HttpBgRequestSent' . PHP_EOL, FILE_APPEND);

            $filePath = __DIR__ . '/../Storage/' . $event->request->id . '_pid.txt';
            file_put_contents($filePath, $event->request->pid);
        });

        Event::listen(function (HttpBgRequestComplete $event) {
            $filePath = __DIR__ . '/../Storage/' . $event->requestId . '_events.txt';
            file_put_contents($filePath, 'HttpBgRequestComplete' . PHP_EOL, FILE_APPEND);
        });

        Event::listen(function (HttpBgRequestSuccess $event) {
            $filePath = __DIR__ . '/../Storage/' . $event->requestId . '_events.txt';
            file_put_contents($filePath, 'HttpBgRequestSuccess' . PHP_EOL, FILE_APPEND);
        });

        Event::listen(function (HttpBgRequestFailed $event) {
            $filePath = __DIR__ . '/../Storage/' . $event->requestId . '_events.txt';
            file_put_contents($filePath, 'HttpBgRequestFailed' . PHP_EOL, FILE_APPEND);
        });

        Event::listen(function (HttpBgRequestTimeout $event) {
            $filePath = __DIR__ . '/../Storage/' . $event->requestId . '_events.txt';
            file_put_contents($filePath, 'HttpBgRequestTimeout' . PHP_EOL, FILE_APPEND);
        });
    }
}
