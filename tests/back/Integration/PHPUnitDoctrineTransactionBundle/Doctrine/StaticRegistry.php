<?php

declare(strict_types=1);

namespace Akeneo\Test\PHPUnitDoctrineTransactionBundle\Doctrine;

use Doctrine\DBAL\Driver\Connection;

class StaticRegistry
{
    /**
     * @var StaticConnection[]
     */
    private static array $connections = [];
    private static bool $isEnabled = false;

    /**
     * @param array<string, mixed> $params
     */
    public static function registerConnection(Connection $connection, array $params): StaticConnection
    {
        $key = sha1(serialize($params));

        if (!isset(static::$connections[$key])) {
            static::$connections[$key] = new StaticConnection($connection);
        }

        return static::$connections[$key];
    }

    public static function enable(): void
    {
        static::$isEnabled = true;
    }

    public static function disable(): void
    {
        static::$isEnabled = false;
    }

    public static function isEnabled(): bool
    {
        return static::$isEnabled;
    }

    public static function reset(): void
    {
        foreach (self::$connections as $connection) {
            try {
                $connection->rollBack();
            } catch (\PDOException $e) {
                // It happens that the opened transaction was automatically commited by mysql
                // For example, "TRUNCATE TABLE x" will commit the transaction.
                // see https://dev.mysql.com/doc/refman/8.0/en/implicit-commit.html
                // When it happens, it's normal we can't rollback anymore so we can ignore the error.
                if ($e->getMessage() !== 'There is no active transaction') {
                    throw $e;
                }
            }

            $connection->beginTransaction();
        }
    }

    public static function beginTransaction(): void
    {
        foreach (self::$connections as $connection) {
            $connection->beginTransaction();
        }
    }

    public static function rollBack(): void
    {
        foreach (self::$connections as $connection) {
            $connection->rollBack();
        }
    }

    public static function commit(): void
    {
        foreach (self::$connections as $connection) {
            $connection->commit();
        }
    }
}
