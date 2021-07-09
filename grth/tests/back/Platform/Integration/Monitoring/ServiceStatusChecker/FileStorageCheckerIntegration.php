<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Monitoring\ServiceStatusChecker;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\DbTableLogHandler;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ElasticsearchChecker;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\FileStorageChecker;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ServiceStatus;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\EnabledConfigurationRepository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Akeneo\Test\Integration\TestCase;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Monolog\Logger;
use PHPUnit\Framework\Assert;

final class FileStorageCheckerIntegration extends TestCase
{
    public function test_filestorage_is_ok_when_when_you_can_create_a_directory_in_each_filestorage(): void
    {
        Assert::assertEquals(ServiceStatus::ok(), $this->getFilestorageChecker()->status());
    }

    public function test_filestorage_is_ko_when_when_you_cant_create_a_directory_in_at_least_one_of_the_filestorage(): void
    {
        $this->getMountManager()->mountFilesystem('catalogStorage', new Filesystem(new NullAdapter()));

        Assert::assertEquals(
            ServiceStatus::notOk('Failing file storages: catalogStorage'),
            $this->getFilestorageChecker()->status()
        );
    }

    protected function getConfiguration()
    {
        return null;
    }

    private function getFilestorageChecker(): FileStorageChecker
    {
        return $this->get('Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\FileStorageChecker');
    }

    private function getMountManager(): MountManager
    {
        return $this->get('oneup_flysystem.mount_manager');
    }
}
