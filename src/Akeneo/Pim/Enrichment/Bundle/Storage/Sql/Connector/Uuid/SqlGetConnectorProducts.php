<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Connector\Uuid;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetGroupAssociationsByProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetProductAssociationsByProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetProductModelAssociationsByProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetCategoryCodesByProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetValuesAndPropertiesFromProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation\GetProductModelQuantifiedAssociationsByProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\QuantifiedAssociation\GetProductQuantifiedAssociationsByProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\Uuid\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\Uuid\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ReadValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Query\Uuid\GetConnectorProducts;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @author    Adrien Migaire <adrien.migaire@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetConnectorProducts implements GetConnectorProducts
{
    public function __construct(
        private GetValuesAndPropertiesFromProductUuids $getValuesAndPropertiesFromProductUuids,
        private GetProductAssociationsByProductUuids $getProductAssociationsByProductUuids,
        private GetProductModelAssociationsByProductUuids $getProductModelAssociationsByProductUuids,
        private GetGroupAssociationsByProductUuids $getGroupAssociationsByProductUuids,
        private GetProductQuantifiedAssociationsByProductUuids $getProductQuantifiedAssociationsByProductUuids,
        private GetProductModelQuantifiedAssociationsByProductUuids $getProductModelQuantifiedAssociationsByProductUuids,
        private GetCategoryCodesByProductUuids $getCategoryCodesByProductUuids,
        private ReadValueCollectionFactory $readValueCollectionFactory
    ) {
    }

    public function fromProductUuid(UuidInterface $productUuid, int $userId): ConnectorProduct
    {
        $products = $this->fromProductUuids([$productUuid], $userId, null, null, null);
        if ($products->totalNumberOfProducts() === 0) {
            throw new ObjectNotFoundException(sprintf('Product "%s" was not found.', $productUuid));
        }

        return $products->connectorProducts()[0];
    }

    /**
     * @param array<UuidInterface> $productUuids
     * @param int $userId
     * @param array|null $attributesToFilterOn
     * @param string|null $channelToFilterOn
     * @param array|null $localesToFilterOn
     * @return ConnectorProductList
     * @throws \Doctrine\DBAL\Exception
     */
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
        foreach ($productUuids as $uuid) {
            if (!isset($rows[$uuid->toString()]['raw_values'])) {
                continue;
            }

            $rawValues = $rows[$uuid->toString()]['raw_values'];

            if (null !== $attributesToFilterOn) {
                $rawValues = $this->filterByAttributeCodes($rawValues, $attributesToFilterOn);
            }
            if (null !== $channelToFilterOn) {
                $rawValues = $this->filterByChannelCode($rawValues, $channelToFilterOn);
            }
            if (null !== $localesToFilterOn) {
                $rawValues = $this->filterByLocaleCodes($rawValues, $localesToFilterOn);
            }

            $rows[$uuid->toString()]['raw_values'] = $rawValues;
            $rawValuesIndexedByProductUuid[$uuid->toString()] = $rawValues;
        }

        $filteredRawValuesIndexedByProductUuid = $this->readValueCollectionFactory->createMultipleFromStorageFormat($rawValuesIndexedByProductUuid);

        $products = [];
        foreach ($productUuids as $uuid) {
            if (!isset($rows[$uuid->toString()]['identifier'])) {
                continue;
            }
            $row = $rows[$uuid->toString()];

            $products[] = new ConnectorProduct(
                Uuid::fromString($row['uuid']),
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
                $filteredRawValuesIndexedByProductUuid[$uuid->toString()],
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
     * @param array<UuidInterface> $productUuids
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchCategoryCodesIndexedByProductUuids(array $productUuids): array
    {
        $categoryCodes = [];
        foreach ($this->getCategoryCodesByProductUuids->fetchCategoryCodes($productUuids)
                 as $productUuid => $productCategoryCodes) {
            $categoryCodes[$productUuid] = ['category_codes' => $productCategoryCodes];
        }

        return $categoryCodes;
    }

    /**
     * @param array<UuidInterface> $productUuids
     * @return array
     */
    private function fetchAssociationsIndexedByProductUuids(array $productUuids): array
    {
        $associations = array_replace_recursive(
            $this->getProductAssociationsByProductUuids->fetchByProductUuids($productUuids),
            $this->getProductModelAssociationsByProductUuids->fetchByProductUuids($productUuids),
            $this->getGroupAssociationsByProductUuids->fetchByProductUuids($productUuids)
        );

        $associationsIndexedByUuid = [];
        foreach ($associations as $uuid => $association) {
            $associationsIndexedByUuid[$uuid]['associations'] = $association;
        }

        return $associationsIndexedByUuid;
    }

    /**
     * @param array<UuidInterface> $productUuids
     * @return array
     */
    private function fetchQuantifiedAssociationsIndexedByProductUuids(array $productUuids): array
    {
        $quantifiedAssociations = array_replace_recursive(
            $this->getProductQuantifiedAssociationsByProductUuids->fromProductUuids($productUuids),
            $this->getProductModelQuantifiedAssociationsByProductUuids->fromProductUuids($productUuids),
        );

        $quantifiedAssociationsIndexedByUuid = [];
        foreach ($quantifiedAssociations as $uuid => $quantifiedAssociation) {
            $associationTypes = array_keys($quantifiedAssociation);
            $quantifiedAssociationsWithoutEntities = array_fill_keys($associationTypes, ['products' => [], 'product_models' => []]);
            $quantifiedAssociation = array_merge_recursive($quantifiedAssociationsWithoutEntities, $quantifiedAssociation);

            $quantifiedAssociationsIndexedByUuid[$uuid]['quantified_associations'] = $quantifiedAssociation;
        }

        return $quantifiedAssociationsIndexedByUuid;
    }
}
