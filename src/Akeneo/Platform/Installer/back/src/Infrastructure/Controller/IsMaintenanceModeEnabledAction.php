<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Controller;

use Akeneo\Platform\Installer\Application\IsMaintenanceModeEnabled\IsMaintenanceModeEnabledHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class IsMaintenanceModeEnabledAction
{
    public function __construct(
        private readonly IsMaintenanceModeEnabledHandler $isMaintenanceModeEnabledHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $isEnabled = $this->isMaintenanceModeEnabledHandler->handle();

        return new JsonResponse($isEnabled, Response::HTTP_OK);
    }
}
