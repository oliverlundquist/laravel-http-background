<?php declare(strict_types=1);

namespace OliverLundquist\HttpBackground\Facades;

use Illuminate\Support\Facades\Facade;
use OliverLundquist\HttpBackground\HttpBg as RealHttpBg;
use OliverLundquist\HttpBackground\HttpBgRequest;

/**
 * @method static RealHttpBg    get(string $url)
 * @method static RealHttpBg    head(string $url)
 * @method static RealHttpBg    post(string $url, ?string $body = null)
 * @method static RealHttpBg    patch(string $url, ?string $body = null)
 * @method static RealHttpBg    put(string $url, ?string $body = null)
 * @method static RealHttpBg    delete(string $url, ?string $body = null)
 * @method static RealHttpBg    send(HttpBgRequest $request)
 * @method static RealHttpBg    queue()
 * @method static RealHttpBg    withBody(string $content, string $contentType = 'application/json', string $accept = 'application/json')
 * @method static RealHttpBg    contentType(string $contentType)
 * @method static RealHttpBg    accept(string $accept)
 * @method static RealHttpBg    connectTimeout(int $seconds)
 * @method static RealHttpBg    timeout(int $seconds)
 * @method static RealHttpBg    retry(int $times)
 * @method static RealHttpBg    setRequestTag(string $tag)
 * @method static HttpBgRequest getRequest()
 * @method static RealHttpBg    setRequest(HttpBgRequest $request)
 * @method static bool          processIsRunning()
 *
 * @see \OliverLundquist\HttpBackground\HttpBg
 */
final class HttpBg extends Facade
{
    /**
     * @return class-string
     */
    protected static function getFacadeAccessor(): string
    {
        return RealHttpBg::class;
    }
}
