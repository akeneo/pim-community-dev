<?php

declare(strict_types=1);

namespace Akeneo\Test\PHPUnitDoctrineTransactionBundle\Doctrine;

use Doctrine\DBAL\Driver\Connection;

class StaticDoctrineRegistry
{
    /**
     * @var ConnectionDecorator[]
     */
    private static array $connections = [];
    private static ?bool $enabled;

    public static function isExperimentalTestDatabaseEnabled(): bool
    {
        return static::$enabled ??= (bool) getenv('EXPERIMENTAL_TEST_DATABASE');
    }

    /**
     * @param array<string, mixed> $params
     */
    public static function registerConnection(Connection $connection, array $params): ConnectionDecorator
    {
        $key = sha1(serialize($params));

        if (!isset(static::$connections[$key])) {
            static::$connections[$key] = new ConnectionDecorator($connection);
        }

        return static::$connections[$key];
    }

    public static function reset(): void
    {
        foreach (self::$connections as $connection) {
            $connection->rollBack();
            $connection->beginTransaction();
        }
    }
}
