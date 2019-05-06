<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Connector;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetCategoryCodesByProductIdentifiers;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetGroupAssociationsByProductIdentifiers;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetProductAssociationsByProductIdentifiers;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetProductModelAssociationsByProductIdentifiers;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetValuesAndPropertiesFromProductIdentifiers;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetConnectorProducts implements Query\GetConnectorProducts
{
    /** @var GetValuesAndPropertiesFromProductIdentifiers */
    private $getValuesAndPropertiesFromProductIdentifiers;

    /** @var GetProductAssociationsByProductIdentifiers */
    private $getProductAssociationsByProductIdentifiers;

    /** @var GetProductModelAssociationsByProductIdentifiers */
    private $getProductModelAssociationsByProductIdentifiers;

    /** @var GetGroupAssociationsByProductIdentifiers */
    private $getGroupAssociationsByProductIdentifiers;

    /** @var GetCategoryCodesByProductIdentifiers */
    private $getCategoryCodesByProductIdentifiers;

    /** @var ValueCollectionFactoryInterface */
    private $valueCollectionFactory;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        GetValuesAndPropertiesFromProductIdentifiers $getValuesAndPropertiesFromProductIdentifiers,
        GetProductAssociationsByProductIdentifiers $getProductAssociationsByProductIdentifiers,
        GetProductModelAssociationsByProductIdentifiers $getProductModelAssociationsByProductIdentifiers,
        GetGroupAssociationsByProductIdentifiers $getGroupAssociationsByProductIdentifiers,
        GetCategoryCodesByProductIdentifiers $getCategoryCodesByProductIdentifiers,
        ValueCollectionFactoryInterface $valueCollectionFactory,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->getValuesAndPropertiesFromProductIdentifiers = $getValuesAndPropertiesFromProductIdentifiers;
        $this->getProductAssociationsByProductIdentifiers = $getProductAssociationsByProductIdentifiers;
        $this->getProductModelAssociationsByProductIdentifiers = $getProductModelAssociationsByProductIdentifiers;
        $this->getGroupAssociationsByProductIdentifiers = $getGroupAssociationsByProductIdentifiers;
        $this->getCategoryCodesByProductIdentifiers = $getCategoryCodesByProductIdentifiers;
        $this->valueCollectionFactory = $valueCollectionFactory;
        $this->attributeRepository = $attributeRepository;
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

        $identifierAttributeCode = $this->attributeRepository->getIdentifierCode();

        $rows = array_replace_recursive(
            $this->getValuesAndPropertiesFromProductIdentifiers->fetchByProductIdentifiers($identifiers),
            $this->fetchAssociationsIndexedByProductIdentifier($identifiers),
            $this->fetchCategoryCodesIndexedByProductIdentifier($identifiers)
        );

        $products = [];
        foreach ($identifiers as $identifier) {
            if (!isset($rows[$identifier])) {
                continue;
            }
            $row = $rows[$identifier];
            $rawValues = $row['raw_values'];

            $rawValues = $this->removeIdentifierValue($rawValues, $identifierAttributeCode);
            if (null !== $attributesToFilterOn) {
                $rawValues = $this->filterByAttributeCodes($rawValues, $attributesToFilterOn);
            }
            if (null !== $channelToFilterOn) {
                $rawValues = $this->filterByChannelCode($rawValues, $channelToFilterOn);
            }
            if (null !== $localesToFilterOn) {
                $rawValues = $this->filterByLocaleCodes($rawValues, $localesToFilterOn);
            }

            $products[] = new ConnectorProduct(
                $row['id'],
                $row['identifier'],
                $row['created'],
                $row['updated'],
                $row['is_enabled'],
                $row['family_code'],
                $row['category_codes'],
                $row['group_codes'],
                $row['product_model_code'],
                $row['associations'],
                [],
                $this->valueCollectionFactory->createFromStorageFormat($rawValues)
            );
        }

        return new ConnectorProductList($result->count(), $products);
    }

    private function removeIdentifierValue($raw_values, $identifierAttributeCode)
    {
        unset($raw_values[$identifierAttributeCode]);

        return $raw_values;
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

    private function fetchCategoryCodesIndexedByProductIdentifier(array $identifiers): array
    {
        $categoryCodes = [];
        foreach ($this->getCategoryCodesByProductIdentifiers->fetchCategoryCodes($identifiers)
                 as $productIdentifier => $productCategoryCodes) {
            $categoryCodes[$productIdentifier] = ['category_codes' => $productCategoryCodes];
        }

        return $categoryCodes;
    }

    private function fetchAssociationsIndexedByProductIdentifier(array $identifiers): array
    {
        $associations = array_replace_recursive(
            $this->getProductAssociationsByProductIdentifiers->fetchByProductIdentifiers($identifiers),
            $this->getProductModelAssociationsByProductIdentifiers->fetchByProductIdentifiers($identifiers),
            $this->getGroupAssociationsByProductIdentifiers->fetchByProductIdentifier($identifiers)
        );

        $associationsIndexedByIdentifier = [];
        foreach ($associations as $identifier => $association) {
            $associationsIndexedByIdentifier[$identifier]['associations'] = $association;
        }

        return $associationsIndexedByIdentifier;
    }
}
