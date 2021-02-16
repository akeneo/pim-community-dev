<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * It is possible to create a lot of collisions in an PHP array by a malicious user.
 * Doing it allows to DDoS easily and almost without any resource a server.
 *
 * To prevent it, it's important to protect the size sent in the JSON body, when the user is not authenticated.
 * Do note that $_GET and $_POST variables are already protected by the PHP setting `max_input_vars`.
 * Therefore, `application/x-www-form-urlencoded` content-type is not concerned by this vulnerability.
 *
 * The goal of this Listener is to prevent it.
 *
 * See:
 * https://nikic.github.io/2011/12/28/Supercolliding-a-PHP-array.html
 * https://www.securityweek.com/hash-table-collision-attacks-could-trigger-ddos-massive-scale
 */
final class CheckApiRequestContentSizeListener
{
    // Maximum allowed request content size in bytes
    private const MAX_CONTENT_SIZE = 300;

    private const API_AUTH_ROUTE = 'fos_oauth_server_token';

    public function onKernelRequest(KernelEvent $event)
    {
        $request = $event->getRequest();

        if (self::API_AUTH_ROUTE !== $request->get('_route')) {
            return;
        }

        if (self::MAX_CONTENT_SIZE < strlen($request->getContent())) {
            throw new HttpException(
                Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
                sprintf('Request content exceeded the maximum allowed size of %s bytes', self::MAX_CONTENT_SIZE)
            );
        }
    }
}
