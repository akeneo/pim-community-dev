<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This subscriber will watch the API auth path and throw a 413 if the request content size is too large.
 * This is to mitigate DDoS attacks on this unrestricted endpoint.
 */
final class CheckRequestContentSizeSubscriber implements EventSubscriberInterface
{
    // Maximum allowed request content size (in bytes)
    private const MAX_CONTENT_SIZE = 300;

    private const API_AUTH_PATH = '/api/oauth/v1/token';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    public function onKernelRequest($event)
    {
        $request = $event->getRequest();

        if (false === strpos($request->getPathInfo(), self::API_AUTH_PATH)) {
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
