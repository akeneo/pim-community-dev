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
use Akeneo\AssetManager\Domain\Query\Asset\Connector\ConnectorAsset;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\FindConnectorAssetByAssetFamilyAndCodeInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\ConnectorAssetHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindConnectorAssetByAssetFamilyAndCode implements FindConnectorAssetByAssetFamilyAndCodeInterface
{
    private Connection $connection;

    private FindValueKeyCollectionInterface $findValueKeyCollection;

    private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier;

    private ConnectorAssetHydrator $assetHydrator;

    public function __construct(
        Connection $connection,
        ConnectorAssetHydrator $hydrator,
        FindValueKeyCollectionInterface $findValueKeyCollection,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->connection = $connection;
        $this->findValueKeyCollection = $findValueKeyCollection;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
        $this->assetHydrator = $hydrator;
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $assetCode): ?ConnectorAsset
    {
        $sql = <<<SQL
            SELECT 
                identifier, 
                code, 
                asset_family_identifier, 
                value_collection,
                created_at,
                updated_at
            FROM akeneo_asset_manager_asset
            WHERE 
                code = :code AND asset_family_identifier = :asset_family_identifier;
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'code' => (string) $assetCode,
                'asset_family_identifier' => (string) $assetFamilyIdentifier,
            ]
        );
        $result = $statement->fetch();

        if (empty($result)) {
            return null;
        }

        return $this->hydrateAsset($result);
    }

    private function hydrateAsset(array $result): ConnectorAsset
    {
        $assetFamilyIdentifier = $this->getAssetFamilyIdentifier($result);
        $valueKeyCollection = $this->findValueKeyCollection->find($assetFamilyIdentifier);
        $indexedAttributes = $this->findAttributesIndexedByIdentifier->find($assetFamilyIdentifier);

        return $this->assetHydrator->hydrate($result, $valueKeyCollection, $indexedAttributes);
    }

    private function getAssetFamilyIdentifier($result): AssetFamilyIdentifier
    {
        if (!isset($result['asset_family_identifier'])) {
            throw new \LogicException('The asset should have an asset family identifier');
        }
        $normalizedAssetFamilyIdentifier = Type::getType(Type::STRING)->convertToPHPValue(
            $result['asset_family_identifier'],
            $this->connection->getDatabasePlatform()
        );

        return AssetFamilyIdentifier::fromString($normalizedAssetFamilyIdentifier);
    }
}
