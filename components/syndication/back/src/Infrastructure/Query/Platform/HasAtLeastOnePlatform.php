<?php

namespace Akeneo\Platform\Syndication\Infrastructure\Query\Platform;

use Akeneo\Platform\Syndication\Domain\Query\Platform\HasAtLeastOnePlatformInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableNotFoundException;

class HasAtLeastOnePlatform implements HasAtLeastOnePlatformInterface
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(): bool
    {
        try {
            $sql = <<<SQL
                SELECT 1
                FROM akeneo_syndication_connected_channel
                LIMIT 1
            SQL;

            $statement = $this->connection->executeQuery(
                $sql
            );

            $result = $statement->fetchOne();

            return false !== $result;
        } catch (TableNotFoundException $exception) {
            return false;
        }
    }
}
