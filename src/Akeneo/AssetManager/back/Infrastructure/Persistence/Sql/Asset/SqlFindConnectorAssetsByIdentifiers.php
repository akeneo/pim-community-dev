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
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\ConnectorAsset;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\FindConnectorAssetsByIdentifiersInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\ConnectorAssetHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindConnectorAssetsByIdentifiers implements FindConnectorAssetsByIdentifiersInterface
{
    private Connection $sqlConnection;

    private FindValueKeyCollectionInterface $findValueKeyCollection;

    private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier;

    private ConnectorAssetHydrator $assetHydrator;

    public function __construct(
        Connection $connection,
        ConnectorAssetHydrator $hydrator,
        FindValueKeyCollectionInterface $findValueKeyCollection,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->sqlConnection = $connection;
        $this->findValueKeyCollection = $findValueKeyCollection;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
        $this->assetHydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $identifiers, AssetQuery $assetQuery): array
    {
        $sql = <<<SQL
            SELECT 
                identifier, 
                code, 
                asset_family_identifier, 
                value_collection
            FROM akeneo_asset_manager_asset
            WHERE identifier IN (:identifiers)
            ORDER BY FIELD(identifier, :identifiers);
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $sql,
            ['identifiers' => $identifiers],
            ['identifiers' => Connection::PARAM_STR_ARRAY]
        );
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return empty($results) ? [] : $this->hydrateAssets($results, $assetQuery);
    }

    /**
     * @return ConnectorAsset[]
     */
    private function hydrateAssets(array $results, AssetQuery $assetQuery): array
    {
        $assetFamilyIdentifier = $this->getAssetFamilyIdentifier(current($results));
        $valueKeyCollection = $this->findValueKeyCollection->find($assetFamilyIdentifier);
        $indexedAttributes = $this->findAttributesIndexedByIdentifier->find($assetFamilyIdentifier);

        $hydratedAssets = [];
        foreach ($results as $result) {
            $hydratedAsset = $this->assetHydrator->hydrate($result, $valueKeyCollection, $indexedAttributes);
            $hydratedAssets[] = $this->filterAssetValues($hydratedAsset, $assetQuery);
        }

        return $hydratedAssets;
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

    private function filterAssetValues(ConnectorAsset $connectorAsset, AssetQuery $assetQuery): ConnectorAsset
    {
        $channelReference = $assetQuery->getChannelReferenceValuesFilter();
        if (!$channelReference->isEmpty()) {
            $connectorAsset = $connectorAsset->getAssetWithValuesFilteredOnChannel($channelReference->getIdentifier());
        }

        $localesIdentifiers = $assetQuery->getLocaleIdentifiersValuesFilter();
        if (!$localesIdentifiers->isEmpty()) {
            $connectorAsset = $connectorAsset->getAssetWithValuesFilteredOnLocales($localesIdentifiers);
        }

        return $connectorAsset;
    }
}
