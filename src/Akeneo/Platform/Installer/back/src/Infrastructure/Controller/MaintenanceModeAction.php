<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Controller;

use Akeneo\Platform\Installer\Application\IsMaintenanceModeEnabled\IsMaintenanceModeEnabledHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class MaintenanceModeAction
{
    public function __construct(
        private readonly IsMaintenanceModeEnabledHandler $isMaintenanceModeEnabledHandler,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        if (!$this->isMaintenanceModeEnabledHandler->handle()) {
            throw new AccessDeniedException();
        }

        return new JsonResponse();
    }
}
