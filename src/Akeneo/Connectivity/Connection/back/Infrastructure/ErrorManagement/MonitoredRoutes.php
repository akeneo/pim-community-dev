<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement;

/**
 * Monitored routes from the public API for the collection of business and technical errors.
 */
final class MonitoredRoutes
{
    const ROUTES = [
        'pim_api_product_create',
        'pim_api_product_partial_update',
        'pim_api_product_partial_update_list',
        'pim_api_product_delete',
    ];
}
