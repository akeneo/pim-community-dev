<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\Listener;

use FOS\RestBundle\EventListener\BodyListener;
use Symfony\Component\HttpFoundation\Request;

final class ApiBodyListener extends BodyListener
{
    protected const API_AUTH_ROUTE = 'fos_oauth_server_token';

    /**
     * {@inheritdoc}
     */
    protected function isDecodeable(Request $request)
    {
        return parent::isDecodeable($request) && self::API_AUTH_ROUTE !== $request->get('_route');
    }
}
