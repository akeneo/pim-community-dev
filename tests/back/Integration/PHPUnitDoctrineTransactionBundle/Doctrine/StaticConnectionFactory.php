<?php

declare(strict_types=1);

namespace Akeneo\Test\PHPUnitDoctrineTransactionBundle\Doctrine;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;

class StaticConnectionFactory extends ConnectionFactory
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

        $class = get_class($originalConnection);

        /** @var Connection $connection */
        $connection = new $class(
            $originalConnection->getParams(),
            new StaticDriver($originalConnection->getDriver()),
            $originalConnection->getConfiguration(),
            $originalConnection->getEventManager(),
        );

        if (StaticRegistry::isEnabled()) {
            $connection->beginTransaction();
        }

        return $connection;
    }
}
