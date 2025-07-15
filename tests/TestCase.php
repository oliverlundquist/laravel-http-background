<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground\Tests;

use OliverLundquist\HttpBackground\HttpBg;
use OliverLundquist\HttpBackground\Providers\HttpBgProvider;
use OliverLundquist\HttpBackground\Tests\Providers\HttpBgEventsProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->cleanUpTestFiles();
        $this->beforeApplicationDestroyed(function () {
            $this->cleanUpTestFiles();
        });
        parent::setUp();
    }

    /**
     * @param  \Illuminate\Foundation\Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            HttpBgProvider::class,
            HttpBgEventsProvider::class
        ];
    }

    protected function assertFiredEvents(HttpBg $request, array $events): void
    {
        $requestId = strval($request->getRequest()->id);
        $this->processHasExited($request);

        $filePath    = $this->getBasePathToEventsFile($requestId . '_events.txt');
        $firedEvents = file_get_contents($filePath);
        $this->assertFalse($request->processIsRunning());
        $this->{in_array('HttpBgRequestSending', $events) ? 'assertTrue' : 'assertFalse'}(str_contains($firedEvents, 'HttpBgRequestSending'));
        $this->{in_array('HttpBgRequestSent', $events) ? 'assertTrue' : 'assertFalse'}(str_contains($firedEvents, 'HttpBgRequestSent'));
        $this->{in_array('HttpBgRequestComplete', $events) ? 'assertTrue' : 'assertFalse'}(str_contains($firedEvents, 'HttpBgRequestComplete'));
        $this->{in_array('HttpBgRequestSuccess', $events) ? 'assertTrue' : 'assertFalse'}(str_contains($firedEvents, 'HttpBgRequestSuccess'));
        $this->{in_array('HttpBgRequestFailed', $events) ? 'assertTrue' : 'assertFalse'}(str_contains($firedEvents, 'HttpBgRequestFailed'));
        $this->{in_array('HttpBgRequestTimeout', $events) ? 'assertTrue' : 'assertFalse'}(str_contains($firedEvents, 'HttpBgRequestTimeout'));
    }

    protected function processHasExited(HttpBg $request): bool
    {
        $pid           = intval($request->getRequest()->pid);
        $requestId     = strval($request->getRequest()->id);
        $maxIterations = 100;

        // when running requests in the queue, the pid doesn't get set
        if ($pid === 0) {
            $filePath = $this->getBasePathToEventsFile($requestId . '_pid.txt');
            if (is_file($filePath)) {
                $request->getRequest()->pid = intval(file_get_contents($filePath));
            }
        }
        \Log::info($request->getRequest()->pid);
        $i = 0;
        while ($request->processIsRunning() === true && $i < $maxIterations) {
            $i = $i + 1;
            sleep(1);
        }
        return $i >= $maxIterations ? false : true;
    }

    private function cleanUpTestFiles(): void
    {
        $filePath  = $this->getBasePathToEventsFile('*');
        $fileNames = glob($filePath);
        foreach ($fileNames as $fileName) {
            if (is_file($fileName)) {
                unlink($fileName);
            }
        }
    }

    private function getBasePathToEventsFile(string $append = ''): string
    {
        return __DIR__ . '/Storage/' . $append;
    }
}
