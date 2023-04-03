<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Acceptance\FakeServices;

use Akeneo\Platform\Installer\Application\InstallPim\InstallFixtures;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InMemoryFixturesInstaller implements InstallFixtures
{
    private bool $installed = false;

    public function minimal(): void
    {
        $this->installed = true;
    }

    public function isInstalled(): bool
    {
        return $this->installed;
    }
}
