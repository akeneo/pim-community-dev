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
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
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
        private AttributeRepositoryInterface $attributeRepository,
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
        $identifiers = array_map(function (IdentifierResult $identifier) {
            return $identifier->getIdentifier();
        }, iterator_to_array($result));

        $products = $this->fromProductIdentifiers($identifiers, $userId, $attributesToFilterOn, $channelToFilterOn, $localesToFilterOn);

        // We use the pqb result count in order to keep paginated research working
        return new ConnectorProductList($result->count(), $products->connectorProducts());
    }

    public function fromProductIdentifier(string $productIdentifier, int $userId): ConnectorProduct
    {
        $products = $this->fromProductIdentifiers([$productIdentifier], $userId, null, null, null);
        if ($products->totalNumberOfProducts() === 0) {
            throw new ObjectNotFoundException(sprintf('Product "%s" was not found.', $productIdentifier));
        }

        return $products->connectorProducts()[0];
    }

    public function fromProductIdentifiers(
        array $productIdentifiers,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList {
        $identifierAttributeCode = $this->attributeRepository->getIdentifierCode();

        $productUuids = $this->getProductUuidsFromProductIdentifiers($productIdentifiers);

        $rowsByUuid = array_replace_recursive(
            $this->getValuesAndPropertiesFromProductUuids->fetchByProductUuids($productUuids),
            $this->fetchAssociationsIndexedByProductUuids($productUuids),
            $this->fetchQuantifiedAssociationsIndexedByProductUuids($productUuids),
            $this->fetchCategoryCodesIndexedByProductUuids($productUuids)
        );

        $rows = $this->replaceUuidKeysByIdentifiers($rowsByUuid);

        $rawValuesIndexedByProductIdentifier = [];
        foreach ($productIdentifiers as $identifier) {
            if (!isset($rows[$identifier]['identifier'])) {
                continue;
            }

            $rawValues = $this->removeIdentifierValue($rows[$identifier]['raw_values'], $identifierAttributeCode);
            if (null !== $attributesToFilterOn) {
                $rawValues = $this->filterByAttributeCodes($rawValues, $attributesToFilterOn);
            }
            if (null !== $channelToFilterOn) {
                $rawValues = $this->filterByChannelCode($rawValues, $channelToFilterOn);
            }
            if (null !== $localesToFilterOn) {
                $rawValues = $this->filterByLocaleCodes($rawValues, $localesToFilterOn);
            }

            $rows[$identifier]['raw_values'] = $rawValues;
            $rawValuesIndexedByProductIdentifier[$identifier] = $rawValues;
        }

        $filteredRawValuesIndexedByProductIdentifier = $this->readValueCollectionFactory->createMultipleFromStorageFormat($rawValuesIndexedByProductIdentifier);

        $products = [];
        foreach ($productIdentifiers as $identifier) {
            if (!isset($rows[$identifier]['identifier'])) {
                continue;
            }
            $row = $rows[$identifier];

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
                $filteredRawValuesIndexedByProductIdentifier[$identifier],
                null,
                null
            );
        }

        return new ConnectorProductList(count($products), $products);
    }

    private function removeIdentifierValue(array $rawValues, string $identifierAttributeCode): array
    {
        unset($rawValues[$identifierAttributeCode]);

        return $rawValues;
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
SELECT BIN_TO_UUID(uuid) AS uuid
FROM pim_catalog_product
WHERE identifier IN (:identifiers)
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

    /**
     * @param array<string, array> $resultByUuid
     * @return array<string, array>
     * @throws \Doctrine\DBAL\Exception
     */
    private function replaceUuidKeysByIdentifiers(array $resultByUuid): array
    {
        $sql = <<<SQL
SELECT BIN_TO_UUID(uuid) AS uuid, identifier
FROM pim_catalog_product
WHERE uuid IN (:uuids)
SQL;

        $uuidsAsBytes = array_map(fn (string $uuid): string => Uuid::fromString($uuid)->getBytes(), array_keys($resultByUuid));
        $uuidsToIdentifiers = $this->connection->fetchAllKeyValue($sql, ['uuids' => $uuidsAsBytes], ['uuids' => Connection::PARAM_STR_ARRAY]);

        $result = [];
        foreach ($resultByUuid as $uuid => $object) {
            $result[$uuidsToIdentifiers[$uuid]] = $object;
        }

        return $result;
    }
}
