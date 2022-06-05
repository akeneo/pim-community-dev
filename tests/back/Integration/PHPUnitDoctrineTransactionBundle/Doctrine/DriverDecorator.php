<?php

declare(strict_types=1);

namespace Akeneo\Test\PHPUnitDoctrineTransactionBundle\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\VersionAwarePlatformDriver;

class DriverDecorator implements Driver, VersionAwarePlatformDriver
{
    private Driver $decorated;

    public function __construct(Driver $decorated)
    {
        $this->decorated = $decorated;
    }

    public function connect(
        array $params,
        $username = null,
        $password = null,
        array $driverOptions = []
    ): Driver\Connection {
        $connection = $this->decorated->connect($params, $username, $password, $driverOptions);

        return StaticDoctrineRegistry::registerConnection($connection, $params);
    }

    public function getDatabasePlatform(): AbstractPlatform
    {
        return $this->decorated->getDatabasePlatform();
    }

    public function getSchemaManager(Connection $conn): AbstractSchemaManager
    {
        return $this->decorated->getSchemaManager($conn);
    }

    public function getName(): string
    {
        return $this->decorated->getName();
    }

    public function getDatabase(Connection $conn): string
    {
        return $this->decorated->getDatabase($conn);
    }

    public function createDatabasePlatformForVersion($version): AbstractPlatform
    {
        if ($this->decorated instanceof VersionAwarePlatformDriver) {
            return $this->decorated->createDatabasePlatformForVersion($version);
        }

        return $this->getDatabasePlatform();
    }
}
