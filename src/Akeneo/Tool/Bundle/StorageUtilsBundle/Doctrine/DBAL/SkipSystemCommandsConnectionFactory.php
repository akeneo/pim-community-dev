<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\DBAL;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;

/**
 * Overrides the default connection factory to skip actual connection to the database during system commands like cache warmup
 * The reason why we need this is that we inject doctrine repositories in some listeners attached to low level events that are triggered during cache warm up
 * It was ok with earlier versions of doctrine, but newer versions establish a connection to the server from the repository's constructor
 * This was causing cache:clear and cache:warmup commands to fail when run from a context where the DB is not available
 * This class simply opens an in memory connection in that case
 */
class SkipSystemCommandsConnectionFactory
{
    private const NO_DB_COMMANDS = [
        'list',
        'cache:clear',
        'cache:warmup',
        'pim:installer:assets',
        'lint:container',
    ];

    public function __construct(
        private ConnectionFactory $factory
    ) {
    }

    public function createConnection(array $params, ?Configuration $config = null, ?EventManager $eventManager = null, array $mappingTypes = [])
    {
        if ($this->shouldSkipDB()) {
            $params['driver'] = 'pdo_sqlite';
            $params['memory'] = true;
        }

        return $this->factory->createConnection($params, $config, $eventManager, $mappingTypes);
    }

    private function shouldSkipDB(): bool
    {
        if (!isset($_SERVER['argc']) || !isset($_SERVER['argv'])) {
            // This is not a CLI context
            return false;
        }

        for ($i = 1; $i < $_SERVER['argc']; $i++) {
            if (in_array($_SERVER['argv'][$i], self::NO_DB_COMMANDS)) {
                return true;
            }
        }
        return false;
    }
}
