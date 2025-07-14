<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class HttpBgRequestSuccess
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int|string $requestId)
    {
    }
}
