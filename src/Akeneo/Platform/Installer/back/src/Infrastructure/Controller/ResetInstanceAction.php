<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Controller;

use Akeneo\Platform\Installer\Application\ResetInstance\ResetInstanceCommand;
use Akeneo\Platform\Installer\Application\ResetInstance\ResetInstanceHandler;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResetInstanceAction
{
    public function __construct(
        private readonly ResetInstanceHandler $resetInstanceHandler,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $this->resetInstanceHandler->handle(new ResetInstanceCommand());

        return new JsonResponse();
    }
}
