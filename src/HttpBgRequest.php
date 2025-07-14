<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground;

use Illuminate\Support\Str;

class HttpBgRequest
{
    public int|string $id;
    public int $pid = 0;
    public string $method;
    public string $url;
    public string $requestBody = '';
    public string $contentType = 'application/json';
    public string $tag;
    public int $connectionTimeout = 10;
    public int $totalRequestTimeout = 30;
    public int $attempts = 0;
    public int $maxAttempts = 1;

    final public function __construct()
    {
    }

    /**
     * @param array<string, int|string> $data
     */
    public static function newFromArray(array $data): static
    {
        return (new static)->hydrate($data);
    }

    /**
     * @param array<string, int|string> $data
     */
    public function hydrate(array $data): static
    {
        $request = new static;
        $validProperties = array_keys(get_class_vars(static::class));

        foreach ($data as $key => $value) {
            $key   = strval($key);
            $value = is_numeric($value) ? intval($value) : strval($value);

            if (in_array($key, $validProperties)) {
                $request->{$key} = $value;
            }
            if (in_array(Str::camel($key), $validProperties)) {
                $request->{Str::camel($key)} = $value;
            }
        }
        return $request;
    }

    /**
     * @return array<int, string>
     */
    public function validateRequest(bool $returnErrorsMessages = false): bool|array
    {
        $errors = array_merge(
            $this->validateMethod(strval($this->method)),
            $this->validateUrl(strval($this->url)),
            $this->validateRequestBody(strval($this->requestBody)),
        );
        return $returnErrorsMessages
            ? $errors
            : count($errors) === 0;
    }

    /**
     * @return array<int, string>
     */
    protected function validateMethod(string $method): array
    {
        $errors = [];
        if (strlen($method) === 0) {
            $errors[] = 'Request Method Not Set';
        }
        if (! in_array(strtoupper($method), ['GET', 'HEAD', 'POST', 'PATCH', 'PUT', 'DELETE'])) {
            $errors[] = 'Invalid Method Set';
        }
        return $errors;
    }

    /**
     * @return array<int, string>
     */
    protected function validateUrl(string $url): array
    {
        $errors = [];
        if (strlen($url) === 0) {
            $errors[] = 'Request URL Not Set';
        }
        if (strpos($url, '/') === 0) {
            $url = 'https://nowhere.com' . $url;
        }
        if (! Str::isUrl($url)) {
            $errors[] = 'Invalid URL (Laravel Validation)';
        }
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            $errors[] = 'Invalid URL (filter_var Validation)';
        }
        return $errors;
    }

    /**
     * @return array<int, string>
     */
    protected function validateRequestBody(string $body): array
    {
        $errors = [];
        if (strlen($body) === 0) {
            return [];
        }
        if (function_exists('json_validate') && json_validate($body) === false) {
            $errors[] = 'Invalid JSON Request Body (json_validate)';
        }
        if (is_null(json_decode($body))) {
            $errors[] = 'Invalid JSON Request Body (json_decode)';
        }
        return $errors;
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        $result = [];
        $validProperties = array_keys(get_class_vars(static::class));

        foreach (get_object_vars($this) as $key => $value) {
            /** @var string $key */
            /** @var string|int $value */
            if (in_array($key, $validProperties)) {
                $result[Str::snake($key)] = $value;
            }
        }
        return $result;
    }
}
