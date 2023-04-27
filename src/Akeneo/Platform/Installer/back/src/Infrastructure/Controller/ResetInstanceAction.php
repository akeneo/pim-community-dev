<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Controller;

use Akeneo\Platform\Installer\Application\ResetDatabase\ResetDatabaseCommand;
use Akeneo\Platform\Installer\Application\ResetDatabase\ResetDatabaseHandler;
use Akeneo\Platform\Installer\Domain\Service\FixtureInstallerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResetInstanceAction
{
    public function __construct(
        private readonly ResetDatabaseHandler $resetDatabaseHandler,
        private readonly FixtureInstallerInterface $fixtureInstaller,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $this->resetDatabaseHandler->handle(new ResetDatabaseCommand([]));
        /** To replace by the new application */
        $this->fixtureInstaller->install();

        return new JsonResponse();
    }
}
