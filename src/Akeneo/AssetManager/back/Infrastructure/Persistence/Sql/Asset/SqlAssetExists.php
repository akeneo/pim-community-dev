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

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetExistsInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAssetExists implements AssetExistsInterface
{
    public function __construct(private Connection $sqlConnection)
    {
    }

    public function withIdentifier(AssetIdentifier $assetIdentifier): bool
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_asset_manager_asset
            WHERE identifier = :identifier
        ) as is_existing
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => (string) $assetIdentifier
        ]);

        return $this->isIdentifierExisting($statement);
    }

    public function withAssetFamilyAndCode(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $code): bool
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_asset_manager_asset
            WHERE asset_family_identifier = :assetFamilyIdentifier
            AND code = :code
        ) as is_existing
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'assetFamilyIdentifier' => (string) $assetFamilyIdentifier,
            'code' => (string) $code
        ]);

        return $this->isIdentifierExisting($statement);
    }

    public function withCode(AssetCode $code): bool
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_asset_manager_asset
            WHERE code = :code
        ) as is_existing
SQL;

        $statement = $this->sqlConnection->executeQuery($query, [
            'code' => (string) $code
        ]);

        return $this->isIdentifierExisting($statement);
    }

    private function isIdentifierExisting(Result $statement): bool
    {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetchAssociative();

        return Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);
    }
}
