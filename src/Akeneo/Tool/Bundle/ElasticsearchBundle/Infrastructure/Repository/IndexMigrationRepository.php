<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\Repository;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Domain\Model\IndexMigration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Domain\Query\IndexMigrationRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class IndexMigrationRepository implements IndexMigrationRepositoryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save(IndexMigration $indexMigration): void
    {
        $sql = <<<SQL
            INSERT INTO pim_index_migration (`index_alias`, `hash`, `values`) 
            VALUES (:index_alias, :hash, :values) 
            ON DUPLICATE KEY UPDATE `values`= :values;
        SQL;

        $this->connection->executeUpdate(
            $sql,
            [
                'index_alias' => $indexMigration->getIndexAlias(),
                'hash' => $indexMigration->getIndexConfigurationHash(),
                'values' => $indexMigration->normalize()
            ],
            ['values' => Types::JSON]
        );
    }
}
