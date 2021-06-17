<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindPropertyAccessibleAssetInterface;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\PropertyAccessibleAsset\PropertyAccessibleAssetHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindPropertyAccessibleAsset implements FindPropertyAccessibleAssetInterface
{
    private Connection $sqlConnection;
    private PropertyAccessibleAssetHydrator $accessibleAssetHydrator;
    private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier;

    public function __construct(
        Connection $sqlConnection,
        PropertyAccessibleAssetHydrator $accessibleAssetHydrator,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->accessibleAssetHydrator = $accessibleAssetHydrator;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $assetCode): ?PropertyAccessibleAsset
    {
        $result = $this->fetchResult($assetFamilyIdentifier, $assetCode);

        if (empty($result)) {
            return null;
        }

        return $this->hydrateAsset($result);
    }

    private function fetchResult(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $assetCode): array
    {
        $query = <<<SQL
        SELECT
            asset.code,
            asset.asset_family_identifier,
            asset.value_collection
        FROM akeneo_asset_manager_asset AS asset
        WHERE asset.asset_family_identifier = :family_identifier AND asset.code = :code;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'family_identifier' => (string) $assetFamilyIdentifier,
            'code' => (string) $assetCode,
        ]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return $result ? $result : [];
    }

    private function getAssetFamilyIdentifier($result): AssetFamilyIdentifier
    {
        $normalizedAssetFamilyIdentifier = Type::getType(Types::STRING)->convertToPHPValue(
            $result['asset_family_identifier'],
            $this->sqlConnection->getDatabasePlatform()
        );

        return AssetFamilyIdentifier::fromString($normalizedAssetFamilyIdentifier);
    }

    private function hydrateAsset(array $result): PropertyAccessibleAsset
    {
        $assetFamilyIdentifier = $this->getAssetFamilyIdentifier($result);
        $attributesIndexedByIdentifier = $this->findAttributesIndexedByIdentifier->find($assetFamilyIdentifier);

        return $this->accessibleAssetHydrator->hydrate(
            $result,
            $attributesIndexedByIdentifier
        );
    }
}
