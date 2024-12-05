<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Middleware;

use Akeneo\Tool\Bundle\FileStorageBundle\Auth\StorageSharedKeyCredential;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\UriInterface;

final class ClientFactory
{
    public function create(UriInterface $uri, ?StorageSharedKeyCredential $sharedKeyCredential): Client
    {
        $handlerStack = HandlerStack::create();

        $handlerStack->push(new AddXMsDateHeaderMiddleware());
        $handlerStack->push(new AddXMsVersionMiddleware());
        $handlerStack->push(new AddDefaultQueryParamsMiddleware($uri->getQuery()));

        if ($sharedKeyCredential !== null) {
            $handlerStack->push(new AddAuthorizationHeaderMiddleware($sharedKeyCredential));
        }

        return new Client(['handler' => $handlerStack]);
    }
}
