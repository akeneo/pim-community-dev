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
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetDetails;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetDetailsInterface;
use Akeneo\AssetManager\Domain\Query\Asset\GenerateEmptyValuesInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetDetailsHydratorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindAssetDetails implements FindAssetDetailsInterface
{
    private Connection $sqlConnection;

    private AssetDetailsHydratorInterface $assetDetailsHydrator;

    private GenerateEmptyValuesInterface $generateEmptyValues;

    private FindValueKeyCollectionInterface $findValueKeyCollection;

    private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier;

    public function __construct(
        Connection $sqlConnection,
        AssetDetailsHydratorInterface $assetDetailsHydrator,
        GenerateEmptyValuesInterface $generateEmptyValues,
        FindValueKeyCollectionInterface $findValueKeyCollection,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->assetDetailsHydrator = $assetDetailsHydrator;
        $this->generateEmptyValues = $generateEmptyValues;
        $this->findValueKeyCollection = $findValueKeyCollection;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $assetCode): ?AssetDetails
    {
        $result = $this->fetchResult($assetFamilyIdentifier, $assetCode);

        if (empty($result)) {
            return null;
        }

        return $this->hydrateAssetDetails($result);
    }

    private function fetchResult(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $assetCode): array
    {
        $query = <<<SQL
        SELECT
            asset.identifier,
            asset.code,
            asset.asset_family_identifier,
            asset.value_collection,
            asset.created_at,
            asset.updated_at,
            reference.attribute_as_main_media,
            reference.attribute_as_label
        FROM akeneo_asset_manager_asset AS asset
        INNER JOIN akeneo_asset_manager_asset_family AS reference
            ON reference.identifier = asset.asset_family_identifier
        WHERE code = :code AND asset_family_identifier = :asset_family_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'code' => (string) $assetCode,
            'asset_family_identifier' => (string) $assetFamilyIdentifier,
        ]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return $result ? $result : [];
    }

    private function getAssetFamilyIdentifier($result): AssetFamilyIdentifier
    {
        if (!isset($result['asset_family_identifier'])) {
            throw new \LogicException('The asset should have an asset family identifier');
        }
        $normalizedAssetFamilyIdentifier = Type::getType(Type::STRING)->convertToPHPValue(
            $result['asset_family_identifier'],
            $this->sqlConnection->getDatabasePlatform()
        );

        return AssetFamilyIdentifier::fromString($normalizedAssetFamilyIdentifier);
    }

    private function hydrateAssetDetails($result): AssetDetails
    {
        $assetFamilyIdentifier = $this->getAssetFamilyIdentifier($result);
        $valueKeyCollection = $this->findValueKeyCollection->find($assetFamilyIdentifier);
        $attributesIndexedByIdentifier = $this->findAttributesIndexedByIdentifier->find($assetFamilyIdentifier);
        $emptyValues = $this->generateEmptyValues->generate($assetFamilyIdentifier);

        return $this->assetDetailsHydrator->hydrate(
            $result,
            $emptyValues,
            $valueKeyCollection,
            $attributesIndexedByIdentifier
        );
    }
}
