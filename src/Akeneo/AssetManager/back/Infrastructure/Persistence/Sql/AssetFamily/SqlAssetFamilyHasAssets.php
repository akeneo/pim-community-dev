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
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlAssetFamilyHasAssets implements AssetFamilyHasAssetsInterface
{
    public function __construct(private Connection $sqlConnection)
    {
    }

    public function hasAssets(AssetFamilyIdentifier $identifier): bool
    {
        $statement = $this->executeQuery($identifier);

        return $this->doesAssetFamilyHaveAssets($statement);
    }

    private function executeQuery(AssetFamilyIdentifier $assetFamilyIdentifier): Result
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

    private function doesAssetFamilyHaveAssets(Result $statement): bool
    {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetchAssociative();

        return Type::getType(Types::BOOLEAN)->convertToPhpValue($result['has_assets'], $platform);
    }
}
