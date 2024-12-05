<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Middleware;

use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\RequestInterface;

final class AddDefaultQueryParamsMiddleware
{
    public function __construct(
        private readonly string $defaultQuery,
    ) {
    }

    public function __invoke(callable $handler): \Closure
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $newUri = $request->getUri()->withQuery(
                Query::build([
                    ...Query::parse($this->defaultQuery),
                    ...Query::parse($request->getUri()->getQuery()),
                ]),
            );

            return $handler($request->withUri($newUri), $options);
        };
    }
}
