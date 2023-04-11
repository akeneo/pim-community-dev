<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Application\InstallPim;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InstallPimHandler
{
    public function __construct(
        private readonly PimStatus $pimStatus,
        private readonly RequirementsCheckerInterface $requirementsChecker,
        private readonly InstallFixtures $installFixtures,
    ) {
    }

    public function handle(InstallPimCommand $installPimCommand): void
    {
        if (false === $installPimCommand->force && $this->isPimAlreadyInstalled()) {
            throw new \RuntimeException('Akeneo PIM is already installed.');
        }

        $this->requirementsChecker->check();
        $this->installFixtures->minimal();
    }

    private function isPimAlreadyInstalled(): bool
    {
        return $this->pimStatus->isPimInstalled();
    }
}
