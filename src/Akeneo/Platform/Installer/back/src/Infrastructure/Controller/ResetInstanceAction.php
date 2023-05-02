<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Controller;

use Akeneo\Platform\Installer\Application\PurgeInstance\PurgeInstanceCommand;
use Akeneo\Platform\Installer\Application\PurgeInstance\PurgeInstanceHandler;
use Akeneo\Platform\Installer\Domain\Service\FixtureInstallerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResetInstanceAction
{
    public function __construct(
        private readonly PurgeInstanceHandler $purgeInstanceHandler,
        private readonly FixtureInstallerInterface $fixtureInstaller,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $this->purgeInstanceHandler->handle(new PurgeInstanceCommand());
        /** To replace by the new handler/command */
        $this->fixtureInstaller->install();

        return new JsonResponse();
    }
}
