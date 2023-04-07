<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Application\InstallPim;

/**
 * This is a dummy class to get runnable tests
 * Feel free to remove or reuse it in RAB-1323.
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InstallPimHandler
{
    public function __construct(
        private readonly InstallFixtures $installFixtures,
    ) {
    }

    public function handle(): void
    {
        $this->installFixtures->minimal();
    }
}
