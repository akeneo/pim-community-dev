<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Middleware;

use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\ApiVersion;
use Psr\Http\Message\RequestInterface;

final class AddXMsVersionMiddleware
{
    public function __invoke(callable $handler): \Closure
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $request = $request->withHeader('x-ms-version', ApiVersion::LATEST->value);

            return $handler($request, $options);
        };
    }
}
