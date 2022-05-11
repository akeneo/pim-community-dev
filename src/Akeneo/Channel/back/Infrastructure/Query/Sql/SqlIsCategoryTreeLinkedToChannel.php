<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Query\Sql;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\IsCategoryTreeLinkedToChannel;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlIsCategoryTreeLinkedToChannel implements IsCategoryTreeLinkedToChannel
{
    private Connection $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function byCategoryTreeId(int $categoryTreeId): bool
    {
        $sql = <<<SQL
        SELECT EXISTS (
            SELECT id
            FROM pim_catalog_channel
            WHERE category_id = :treeId
        )
        SQL;

        $exists = $this->connection->executeQuery(
            $sql,
            [
                'treeId' => $categoryTreeId
            ]
        )->fetchOne();

        return (bool) $exists;
    }
}
