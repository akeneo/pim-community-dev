<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\External;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @internal This is an undocumented API endpoint used for internal purposes only
 * Endpoint not implemented but is necessary as HAL paginator requires a route for a single item
 */
final class GetTestAppAction
{
    public function __construct()
    {
    }

    public function __invoke(): Response
    {
        throw new NotFoundHttpException();
    }
}
