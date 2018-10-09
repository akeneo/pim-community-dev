<?php

namespace AkeneoTest\Platform\Integration\Installer;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class InstallStatusManagerIntegration extends TestCase
{
    public function testGetPimInstallTimestamp()
    {
        $installStatusManager = $this->get('pim_installer.install_status_manager');
        $timestamp = $installStatusManager->getPimInstallDateTime();
        $this->assertNotNull($timestamp);
        $this->assertInstanceOf(\DateTime::class, $timestamp);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
