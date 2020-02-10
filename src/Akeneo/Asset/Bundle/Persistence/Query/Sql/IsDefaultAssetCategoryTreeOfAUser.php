<?php

declare(strict_types=1);

namespace Akeneo\Asset\Bundle\Persistence\Query\Sql;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class IsDefaultAssetCategoryTreeOfAUser
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetch(string $assetCategoryTree): bool
    {
        $sql = <<<SQL
SELECT EXISTS (
    SELECT 1
    FROM oro_user
    WHERE JSON_EXTRACT(properties, '$.default_asset_tree') = :asset_category_tree_code
) as is_existing
SQL;
        $result = $this->connection->executeQuery($sql, ['asset_category_tree_code' => $assetCategoryTree])->fetch();
        $platform = $this->connection->getDatabasePlatform();
        $isExisting = Type::getType(Type::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);

        return $isExisting;
    }
}
