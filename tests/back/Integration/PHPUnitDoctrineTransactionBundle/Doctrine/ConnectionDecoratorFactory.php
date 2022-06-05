<?php

declare(strict_types=1);

namespace Akeneo\Test\PHPUnitDoctrineTransactionBundle\Doctrine;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;

class ConnectionDecoratorFactory extends ConnectionFactory
{
    private ConnectionFactory $decorated;

    public function __construct(ConnectionFactory $decorated)
    {
        parent::__construct([]);

        $this->decorated = $decorated;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function createConnection(
        array $params,
        Configuration $config = null,
        EventManager $eventManager = null,
        array $mappingTypes = []
    ): Connection {
        $originalConnection = $this->decorated->createConnection($params, $config, $eventManager, $mappingTypes);

        if (!StaticDoctrineRegistry::isExperimentalTestDatabaseEnabled()) {
            return $originalConnection;
        }

        $class = get_class($originalConnection);

        /** @var Connection $connection */
        $connection = new $class(
            $originalConnection->getParams(),
            new DriverDecorator($originalConnection->getDriver()),
            $originalConnection->getConfiguration(),
            $originalConnection->getEventManager(),
        );

        // Make sure we use savepoints to be able to easily roll-back nested transactions
        if ($connection->getDriver()->getDatabasePlatform()->supportsSavepoints()) {
            $connection->setNestTransactionsWithSavepoints(true);
        }

        $connection->beginTransaction();

        return $connection;
    }
}
