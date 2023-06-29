<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Connector;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetGroupAssociationsByProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetProductAssociationsByProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetProductModelAssociationsByProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetCategoryCodesByProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetValuesAndPropertiesFromProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation\GetProductModelQuantifiedAssociationsByProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation\GetProductQuantifiedAssociationsByProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ReadValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Query;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetConnectorProducts implements Query\GetConnectorProducts
{
    public function __construct(
        private GetValuesAndPropertiesFromProductUuids $getValuesAndPropertiesFromProductUuids,
        private GetProductAssociationsByProductUuids $getProductAssociationsByProductUuids,
        private GetProductModelAssociationsByProductUuids $getProductModelAssociationsByProductUuids,
        private GetGroupAssociationsByProductUuids $getGroupAssociationsByProductUuids,
        private GetProductQuantifiedAssociationsByProductUuids $getProductQuantifiedAssociationsByProductUuids,
        private GetProductModelQuantifiedAssociationsByProductUuids $getProductModelQuantifiedAssociationsByProductUuids,
        private GetCategoryCodesByProductUuids $getCategoryCodesByProductUuids,
        private ReadValueCollectionFactory $readValueCollectionFactory,
        private Connection $connection
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductQueryBuilder(
        ProductQueryBuilderInterface $pqb,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList {
        $result = $pqb->execute();
        $uuids = array_map(function (IdentifierResult $identifier) {
            return $this->getUuidFromIdentifierResult($identifier->getId());
        }, iterator_to_array($result));

        $products = $this->fromProductUuids($uuids, $userId, $attributesToFilterOn, $channelToFilterOn, $localesToFilterOn);

        // We use the pqb result count in order to keep paginated research working
        return new ConnectorProductList($result->count(), $products->connectorProducts());
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductIdentifiers(
        array $productIdentifiers,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList {
        return $this->fromProductUuids(
            $this->getProductUuidsFromProductIdentifiers($productIdentifiers),
            $userId,
            $attributesToFilterOn,
            $channelToFilterOn,
            $localesToFilterOn
        );
    }

    public function fromProductUuid(UuidInterface $productUuid, int $userId): ConnectorProduct
    {
        $products = $this->fromProductUuids([$productUuid], $userId, null, null, null);
        if ($products->totalNumberOfProducts() === 0) {
            throw new ObjectNotFoundException(sprintf('Product "%s" was not found.', $productUuid->toString()));
        }

        return $products->connectorProducts()[0];
    }

    public function fromProductUuids(
        array $productUuids,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList {
        $rows = array_replace_recursive(
            $this->getValuesAndPropertiesFromProductUuids->fetchByProductUuids($productUuids),
            $this->fetchAssociationsIndexedByProductUuids($productUuids),
            $this->fetchQuantifiedAssociationsIndexedByProductUuids($productUuids),
            $this->fetchCategoryCodesIndexedByProductUuids($productUuids)
        );

        $rawValuesIndexedByProductUuid = [];
        foreach ($productUuids as $productUuid) {
            if (!isset($rows[$productUuid->toString()]['uuid'])) {
                continue;
            }

            $rawValues = $rows[$productUuid->toString()]['raw_values'];
            if (null !== $attributesToFilterOn) {
                $rawValues = $this->filterByAttributeCodes($rawValues, $attributesToFilterOn);
            }
            if (null !== $channelToFilterOn) {
                $rawValues = $this->filterByChannelCode($rawValues, $channelToFilterOn);
            }
            if (null !== $localesToFilterOn) {
                $rawValues = $this->filterByLocaleCodes($rawValues, $localesToFilterOn);
            }

            $rows[$productUuid->toString()]['raw_values'] = $rawValues;
            $rawValuesIndexedByProductUuid[$productUuid->toString()] = $rawValues;
        }

        $filteredRawValuesIndexedByProductIdentifier = $this->readValueCollectionFactory->createMultipleFromStorageFormat($rawValuesIndexedByProductUuid);

        $products = [];
        foreach ($productUuids as $productUuid) {
            if (!isset($rows[$productUuid->toString()])) {
                continue;
            }
            $row = $rows[$productUuid->toString()];

            // if an unknown uuid is given, it will not have the uuid key
            if (!\key_exists('uuid', $row)) {
                continue;
            }

            $products[] = new ConnectorProduct(
                $row['uuid'],
                $row['identifier'],
                $row['created'],
                $row['updated'],
                $row['is_enabled'],
                $row['family_code'],
                $row['category_codes'],
                $row['group_codes'],
                $row['product_model_code'],
                $row['associations'] ?? [],
                $row['quantified_associations'] ?? [],
                [],
                $filteredRawValuesIndexedByProductIdentifier[$productUuid->toString()],
                null,
                null
            );
        }

        return new ConnectorProductList(count($products), $products);
    }

    private function filterByAttributeCodes(array $rawValues, array $attributeCodes): array
    {
        $result = [];
        foreach ($rawValues as $attributeCode => $attributeValues) {
            if (in_array($attributeCode, $attributeCodes)) {
                $result[$attributeCode] = $attributeValues;
            }
        }

        return $result;
    }

    private function filterByChannelCode(array $rawValues, string $filterScope): array
    {
        $result = [];
        foreach ($rawValues as $attributeCode => $attributeValues) {
            foreach ($attributeValues as $scope => $scopedValue) {
                if ($scope === '<all_channels>' || $scope === $filterScope) {
                    $result[$attributeCode][$scope] = $scopedValue;
                }
            }
        }

        return $result;
    }

    private function filterByLocaleCodes(array $rawValues, array $localesToFilterOn): array
    {
        $result = [];
        foreach ($rawValues as $attributeCode => $attributeValues) {
            foreach ($attributeValues as $scope => $scopedValue) {
                foreach ($scopedValue as $locale => $value) {
                    if ($locale === '<all_locales>' || in_array($locale, $localesToFilterOn)) {
                        $result[$attributeCode][$scope][$locale] = $value;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param array<UuidInterface> $uuids
     */
    private function fetchCategoryCodesIndexedByProductUuids(array $uuids): array
    {
        $categoryCodes = [];
        $categoryCodesByUuid = $this->getCategoryCodesByProductUuids->fetchCategoryCodes($uuids);

        foreach ($categoryCodesByUuid as $productUuid => $productCategoryCodes) {
            $categoryCodes[$productUuid] = ['category_codes' => $productCategoryCodes];
        }

        return $categoryCodes;
    }

    private function fetchAssociationsIndexedByProductUuids(array $uuids): array
    {
        $associations = array_replace_recursive(
            $this->getProductAssociationsByProductUuids->fetchByProductUuids($uuids),
            $this->getProductModelAssociationsByProductUuids->fetchByProductUuids($uuids),
            $this->getGroupAssociationsByProductUuids->fetchByProductUuids($uuids)
        );

        $associationsIndexedByUuid = [];
        foreach ($associations as $uuid => $association) {
            $associationsIndexedByUuid[$uuid]['associations'] = $association;
        }

        return $associationsIndexedByUuid;
    }

    private function fetchQuantifiedAssociationsIndexedByProductUuids(array $uuids): array
    {
        $quantifiedAssociations = array_replace_recursive(
            $this->getProductQuantifiedAssociationsByProductUuids->fromProductUuids($uuids),
            $this->getProductModelQuantifiedAssociationsByProductUuids->fromProductUuids($uuids),
        );

        $quantifiedAssociationsIndexedByUuid = [];
        foreach ($quantifiedAssociations as $uuid => $quantifiedAssociation) {
            $associationTypes = array_map('strval', array_keys($quantifiedAssociation));

            $filledAssociations = [];
            foreach ($associationTypes as $associationType) {
                $filledAssociations[$associationType] = ['product_models' => [], 'products' => []];
                if (\array_key_exists($associationType, $quantifiedAssociation)) {
                    $filledAssociations[$associationType]['products'] = $quantifiedAssociation[$associationType]['products'] ?? [];
                    $filledAssociations[$associationType]['product_models'] = $quantifiedAssociation[$associationType]['product_models'] ?? [];
                }
            }

            $quantifiedAssociationsIndexedByUuid[$uuid]['quantified_associations'] = $filledAssociations;
        }

        return $quantifiedAssociationsIndexedByUuid;
    }

    /**
     * @param array<string> $productIdentifiers
     * @return array<UuidInterface>
     */
    private function getProductUuidsFromProductIdentifiers(array $productIdentifiers): array
    {
        $sql = <<<SQL
WITH main_identifier AS (
    SELECT id
    FROM pim_catalog_attribute
    WHERE main_identifier = 1
    LIMIT 1
)
SELECT BIN_TO_UUID(product_uuid) AS uuid
FROM pim_catalog_product_unique_data
WHERE raw_data IN (:identifiers)
    AND attribute_id = (SELECT id FROM main_identifier) 
SQL;

        return array_map(
            fn (string $uuidStr): UuidInterface => Uuid::fromString($uuidStr),
            $this->connection->fetchFirstColumn(
                $sql,
                ['identifiers' => $productIdentifiers],
                ['identifiers' => Connection::PARAM_STR_ARRAY]
            )
        );
    }

    private function getUuidFromIdentifierResult(string $esId): UuidInterface
    {
        $matches = [];
        if (!\preg_match('/^product_(?P<uuid>[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})$/', $esId, $matches)) {
            throw new \InvalidArgumentException(sprintf('Invalid Elasticsearch identifier %s', $esId));
        }

        return Uuid::fromString($matches['uuid']);
    }
}
