<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground;

use Exception;
use Illuminate\Support\Facades\Log;

class HttpBg
{
    protected ?HttpBgRequest $request = null;
    protected bool $shouldQueueRequest = false;

    public function get(string $url): static
    {
        $request         = $this->getRequest();
        $request->method = 'GET';
        $request->url    = $url;

        $this->sendRequest($request);
        return $this;
    }

    public function head(string $url): static
    {
        $request         = $this->getRequest();
        $request->method = 'HEAD';
        $request->url    = $url;

        $this->sendRequest($request);
        return $this;
    }

    public function post(string $url, ?string $body = null): static
    {
        $request         = $this->getRequest();
        $request->method = 'POST';
        $request->url    = $url;

        if (! is_null($body)) {
            $this->withBody($body);
        }
        $this->sendRequest($request);
        return $this;
    }

    public function patch(string $url, ?string $body = null): static
    {
        $request         = $this->getRequest();
        $request->method = 'PATCH';
        $request->url    = $url;

        if (! is_null($body)) {
            $this->withBody($body);
        }
        $this->sendRequest($request);
        return $this;
    }

    public function put(string $url, ?string $body = null): static
    {
        $request         = $this->getRequest();
        $request->method = 'PUT';
        $request->url    = $url;

        if (! is_null($body)) {
            $this->withBody($body);
        }
        $this->sendRequest($request);
        return $this;
    }

    public function delete(string $url, ?string $body = null): static
    {
        $request         = $this->getRequest();
        $request->method = 'DELETE';
        $request->url    = $url;

        if (! is_null($body)) {
            $this->withBody($body);
        }
        $this->sendRequest($request);
        return $this;
    }

    public function send(HttpBgRequest $request): static
    {
        $this->setRequest($request);
        $this->sendRequest($request);
        return $this;
    }

    public function queue(): static
    {
        $this->shouldQueueRequest = true;
        return $this;
    }

    public function withBody(string $content, string $contentType = 'application/json'): static
    {
        $request = $this->getRequest();
        $request->requestBody = $content;
        $request->contentType = $contentType;
        return $this;
    }

    public function contentType(string $contentType): static
    {
        $request = $this->getRequest();
        $request->contentType = $contentType;
        return $this;
    }

    public function connectTimeout(int $seconds): static
    {
        $request = $this->getRequest();
        $request->connectionTimeout = $seconds;
        return $this;
    }

    public function timeout(int $seconds): static
    {
        $request = $this->getRequest();
        $request->totalRequestTimeout = $seconds;
        return $this;
    }

    public function retry(int $times): static
    {
        $request = $this->getRequest();
        $request->maxAttempts = $times;
        return $this;
    }

    public function setRequestTag(string $tag): static
    {
        $request = $this->getRequest();
        $request->tag = $tag;
        return $this;
    }

    public function getRequest(): HttpBgRequest
    {
        if (! is_null($this->request)) {
            return $this->request;
        }
        $this->request     = new HttpBgRequest;
        $this->request->id = uniqid(class_basename($this->request) . '_');
        return $this->request;
    }

    public function setRequest(HttpBgRequest $request): static
    {
        $this->request = $request;
        return $this;
    }

    public function processIsRunning(): bool {
        $request = $this->getRequest();
        if ($request->pid < 1) {
            return false;
        }

        // check if pid exists
        $pidExists = intval(trim(strval(exec('kill -s 0 ' . $request->pid . ' > /dev/null 2>&1; echo $?')))) === 0 ? true : false;
        if ($pidExists === false) {
            return false;
        }

        // check if process turned into a zombie
        $isZombie = strpos(trim(str_replace(strval($request->pid), '', strval(exec('ps axo pid=,stat= | grep ' . $request->pid)))), 'Z') !== false;
        if ($isZombie) {
            Log::warning(implode(' ', [
                'Process with PID: ' . $request->pid . ' did not exit successfully.',
                'Make sure that PID 1 can reap child processes.',
                'If in Docker, running it with the --init option could resolve this issue.'
            ]));
            return false;
        }
        return true;
    }

    /**
     * @throws \Exception
     */
    protected function sendRequest(HttpBgRequest $request): void
    {
        if ($request->validateRequest() === false) {
            /** @var array<int, string> $errorMessages */
            $errorMessages = $request->validateRequest(true);
            throw new Exception(implode(' | ', $errorMessages));
        }
        $this->shouldQueueRequest === true
            ? HttpBgQueueJob::dispatch($request)
            : HttpBgCommand::execute($request);
    }
}
