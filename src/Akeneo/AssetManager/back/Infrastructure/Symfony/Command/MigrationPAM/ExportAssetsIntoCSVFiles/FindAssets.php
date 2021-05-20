<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command\MigrationPAM\ExportAssetsIntoCSVFiles;

use Doctrine\DBAL\Driver\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 */
class FindAssets
{
    /** * @var Connection */
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetch(): \Iterator
    {
        $this->connection->getWrappedConnection()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $fetchAssetsQuery = <<<SQL
SELECT 'code', 'categories', 'description', 'end_of_use', 'localized', 'tags'
UNION ALL
(
    SELECT
        a.code as code,
        COALESCE(GROUP_CONCAT(DISTINCT c.code), '') AS categories,
        COALESCE(description, ''),
        COALESCE(CAST(end_of_use_at AS DATE), '') AS end_of_use,
        ar.localized,
        COALESCE(GROUP_CONCAT(t.code), '') as tags
    FROM
        pimee_product_asset_asset a
        -- categories
        LEFT JOIN pimee_product_asset_asset_category ac ON a.id = ac.asset_id
        LEFT JOIN pimee_product_asset_category c ON ac.category_id = c.id
        -- tags
        LEFT JOIN pimee_product_asset_asset_tag at ON a.id = at.asset_id
        LEFT JOIN pimee_product_asset_tag t ON at.tag_id = t.id
        -- localized
        LEFT JOIN  (
            SELECT asset_id as id, GROUP_CONCAT(locale_id) IS NOT NULL as localized
            FROM pimee_product_asset_reference
            GROUP BY asset_id
        ) ar ON a.id = ar.id
    GROUP BY a.code, ar.localized
)
;
SQL;

        $stmt = $this->connection->query();
        while ($row = $stmt->fetch()) {
            yield $row;
        }
    }
}
