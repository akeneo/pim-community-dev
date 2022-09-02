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
use Doctrine\DBAL\Types\Types;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindConnectorAssetsByIdentifiers implements FindConnectorAssetsByIdentifiersInterface
{
    public function __construct(
        private Connection $sqlConnection,
        private ConnectorAssetHydrator $assetHydrator,
        private FindValueKeyCollectionInterface $findValueKeyCollection,
        private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $identifiers, AssetQuery $assetQuery): array
    {
        if (empty($identifiers)) {
            return [];
        }

        $sql = <<<SQL
            SELECT
            /*+ SET_VAR( range_optimizer_max_mem_size = 50000000) */
                identifier,
                code,
                asset_family_identifier,
                value_collection,
                created_at,
                updated_at
            FROM akeneo_asset_manager_asset
            WHERE identifier IN (:identifiers);
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $sql,
            ['identifiers' => $identifiers],
            ['identifiers' => Connection::PARAM_STR_ARRAY]
        );
        $assets = $statement->fetchAllAssociative();
        $orderedAssets = $this->orderAssetItems($assets, $identifiers);

        return empty($orderedAssets) ? [] : $this->hydrateAssets($orderedAssets, $assetQuery);
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
        $normalizedAssetFamilyIdentifier = Type::getType(Types::STRING)->convertToPHPValue(
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

    private function orderAssetItems(array $normalizedAssetItems, array $orderedIdentifiers): array
    {
        $resultIndexedByIdentifier = array_column($normalizedAssetItems, null, 'identifier');
        $resultIndexedByIdentifier = array_change_key_case($resultIndexedByIdentifier, CASE_LOWER);

        $existingIdentifiers = [];
        foreach ($orderedIdentifiers as $orderedIdentifier) {
            $sanitizedIdentifier = trim(strtolower($orderedIdentifier));

            if (isset($resultIndexedByIdentifier[$sanitizedIdentifier])) {
                $existingIdentifiers[$sanitizedIdentifier] = $sanitizedIdentifier;
            }
        }

        $result = array_replace($existingIdentifiers, $resultIndexedByIdentifier);

        return array_values($result);
    }
}
