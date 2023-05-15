<?php

namespace AkeneoTest\Platform\Integration\Installer;

use Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\InstallData;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @author  JM Leroux <jmleroux.pro@gmail.com>
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallStatusManagerIntegration extends TestCase
{
    public function test_get_pim_install_datetime()
    {
        $intallDataQuery = $this->get(InstallData::class);
        $intallDataQuery->withDatetime(new \DateTimeImmutable('2022-12-13'));

        $installStatusManager = $this->get('pim_installer.install_status_manager');
        $timestamp = $installStatusManager->getPimInstallDateTime();
        $this->assertNotNull($timestamp);
        $this->assertInstanceOf(\DateTime::class, $timestamp);
    }

    public function test_pim_installed()
    {
        $intallDataQuery = $this->get(InstallData::class);
        $intallDataQuery->withDatetime(new \DateTimeImmutable('2022-12-13'));

        $installStatusManager = $this->get('pim_installer.install_status_manager');
        $this->assertTrue($installStatusManager->isPimInstalled());
    }

    public function test_pim_not_installed_if_no_data_in_configuration_table()
    {
        $installStatusManager = $this->get('pim_installer.install_status_manager');
        $this->assertFalse($installStatusManager->isPimInstalled());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
