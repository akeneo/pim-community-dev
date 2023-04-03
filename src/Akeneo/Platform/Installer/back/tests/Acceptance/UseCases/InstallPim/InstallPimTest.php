<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Acceptance\UseCases\InstallPim;

use Akeneo\Platform\Installer\Application\InstallPim\InstallPimHandler;
use Akeneo\Platform\Installer\Test\Acceptance\FakeServices\InMemoryFixturesInstaller;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InstallPimTest extends KernelTestCase
{
    private InstallPimHandler $handler;
    private InMemoryFixturesInstaller $fixturesInstaller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = self::getContainer()->get('akeneo.installer.handler.install_pim');
        $this->fixturesInstaller = self::getContainer()->get('akeneo.installer.fixtures.installer');
    }

    public function test_it_installs_the_pim(): void
    {
        $this->handler->handle();
        $this->assertTrue($this->fixturesInstaller->isInstalled());
    }
}
