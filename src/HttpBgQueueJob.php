<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class HttpBgQueueJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(protected HttpBgRequest $request)
    {
    }

    public function handle(): void
    {
        HttpBgCommand::execute($this->request);
    }
}
