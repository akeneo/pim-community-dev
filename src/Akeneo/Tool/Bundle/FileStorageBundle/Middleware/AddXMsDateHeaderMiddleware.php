<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Middleware;

use Psr\Http\Message\RequestInterface;

final class AddXMsDateHeaderMiddleware
{
    public function __invoke(callable $handler): \Closure
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $request = $request->withHeader('x-ms-date', gmdate('D, d M Y H:i:s T', time()));

            return $handler($request, $options);
        };
    }
}
