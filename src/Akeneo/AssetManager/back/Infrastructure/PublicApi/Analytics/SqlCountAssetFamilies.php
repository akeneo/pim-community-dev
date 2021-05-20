<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Analytics;

use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlCountAssetFamilies
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function fetch(): CountVolume
    {
        $sql = <<<SQL
            SELECT COUNT(*) as count
            FROM akeneo_asset_manager_asset_family;
SQL;
        $result = $this->sqlConnection->query($sql)->fetch();

        return new CountVolume((int) $result['count']);
    }
}
