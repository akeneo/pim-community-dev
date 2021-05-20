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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyHasAssetsInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlAssetFamilyHasAssets implements AssetFamilyHasAssetsInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function hasAssets(AssetFamilyIdentifier $identifier): bool
    {
        $statement = $this->executeQuery($identifier);

        return $this->doesAssetFamilyHaveAssets($statement);
    }

    private function executeQuery(AssetFamilyIdentifier $assetFamilyIdentifier): Statement
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_asset_manager_asset
            WHERE asset_family_identifier = :asset_family_identifier
        ) as has_assets
SQL;

        return $this->sqlConnection->executeQuery($query, [
            'asset_family_identifier' => $assetFamilyIdentifier,
        ]);
    }

    private function doesAssetFamilyHaveAssets(Statement $statement): bool
    {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        return Type::getType(Type::BOOLEAN)->convertToPhpValue($result['has_assets'], $platform);
    }
}
