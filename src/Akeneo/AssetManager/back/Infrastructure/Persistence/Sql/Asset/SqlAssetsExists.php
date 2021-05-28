<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAssetsExists
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * @param string[] $codes
     *
     * @return string[]
     */
    public function withAssetFamilyAndCodes(AssetFamilyIdentifier $assetFamilyIdentifier, array $codes): array
    {
        $query = <<<SQL
        SELECT code
        FROM akeneo_asset_manager_asset
        WHERE asset_family_identifier = :assetFamilyIdentifier
        AND code IN (:codes)
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'assetFamilyIdentifier' => (string) $assetFamilyIdentifier,
                'codes'                     => $codes,
            ],
            [
                'codes' => Connection::PARAM_STR_ARRAY
            ]
        );

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}
