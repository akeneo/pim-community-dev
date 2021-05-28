<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command\MigrationPAM\ExportAssetsIntoCSVFiles;

use Doctrine\DBAL\Driver\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 */
class FindVariations
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
SELECT 'asset', 'channel', 'locale', 'reference_file', 'variation_file'
UNION ALL
(
  SELECT
	a.code AS asset
	,c.code AS channel
	,l.code AS locale
	,f_r.file_key AS reference
	,f_v.file_key AS variation
        FROM
            pimee_product_asset_asset a
            LEFT JOIN pimee_product_asset_reference r ON r.asset_id = a.id
            INNER JOIN pimee_product_asset_variation v ON v.reference_id = r.id
            INNER JOIN pim_catalog_channel c on c.id = v.channel_id
            LEFT JOIN pim_catalog_locale l on l.id = r.locale_id
            LEFT JOIN akeneo_file_storage_file_info f_r ON f_r.id = r.file_info_id
            LEFT JOIN akeneo_file_storage_file_info f_v ON f_v.id = v.file_info_id
)
;
SQL;

        $stmt = $this->connection->query();
        while ($row = $stmt->fetch()) {
            yield $row;
        }
    }
}
