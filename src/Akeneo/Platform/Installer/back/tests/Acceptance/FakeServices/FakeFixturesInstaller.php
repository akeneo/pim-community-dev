<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Acceptance\FakeServices;

use Akeneo\Platform\Installer\Domain\Service\FixtureInstallerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FakeFixturesInstaller implements FixtureInstallerInterface
{
    private bool $installed = false;

    public function install(): void
    {
        $this->installed = true;
    }

    public function isInstalled(): bool
    {
        return $this->installed;
    }
}
