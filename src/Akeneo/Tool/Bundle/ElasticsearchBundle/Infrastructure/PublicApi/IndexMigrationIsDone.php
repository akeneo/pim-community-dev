<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\PublicApi;

use Akeneo\Tool\Component\Elasticsearch\PublicApi\Read\IndexMigrationIsDoneInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

class IndexMigrationIsDone implements IndexMigrationIsDoneInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byIndexAliasAndHash(string $indexAlias, string $hash): bool
    {
        $sql = <<<SQL
            SELECT EXISTS (
                SELECT 1
                FROM pim_index_migration 
                WHERE index_alias = :index_alias 
                AND hash = :hash
                AND JSON_EXTRACT(`values`, '$.status') = 'done'
            ) as index_migration_is_done
        SQL;

        $statement = $this->connection->executeQuery($sql, [
            'index_alias' => $indexAlias,
            'hash' => $hash,
        ]);

        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return '1' === $result['index_migration_is_done'];
    }
}
