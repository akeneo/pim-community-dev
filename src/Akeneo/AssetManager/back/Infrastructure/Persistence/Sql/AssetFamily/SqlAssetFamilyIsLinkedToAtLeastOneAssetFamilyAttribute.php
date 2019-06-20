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
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyIsLinkedToAtLeastOneAssetFamilyAttributeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlAssetFamilyIsLinkedToAtLeastOneAssetFamilyAttribute implements AssetFamilyIsLinkedToAtLeastOneAssetFamilyAttributeInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function isLinked(AssetFamilyIdentifier $identifier): bool
    {
        return $this->isAssetFamilyLinkedToAtLeastOneAssetFamilyAttribute($identifier);
    }

    private function isAssetFamilyLinkedToAtLeastOneAssetFamilyAttribute(AssetFamilyIdentifier $identifier): bool
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_asset_manager_attribute
            WHERE (
                attribute_type = :assetAttributeType OR
                attribute_type = :assetCollectionAttributeType
            )
            AND JSON_CONTAINS(additional_properties, :jsonAssetType)
        ) as is_linked
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'assetAttributeType' => 'asset',
            'assetCollectionAttributeType' => 'asset_collection',
            'jsonAssetType' => sprintf('{"asset_type": "%s"}', $identifier),
        ]);

        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $isLinked = Type::getType(Type::BOOLEAN)->convertToPhpValue($result['is_linked'], $platform);

        return $isLinked;
    }
}
