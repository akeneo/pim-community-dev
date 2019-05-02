<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetCategoryCodesByProductIdentifiers;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetProductAssociationsByProductIdentifiers;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetValuesAndPropertiesFromProductIdentifiers;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetMetadataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

class GetConnectorProductsFromReadModel implements GetConnectorProducts
{
    /** @var GetValuesAndPropertiesFromProductIdentifiers */
    private $getValuesAndPropertiesFromProductIdentifiers;

    /** @var GetProductAssociationsByProductIdentifiers */
    private $getProductAssociationsByProductIdentifiers;

    /** @var GetCategoryCodesByProductIdentifiers */
    private $getCategoryCodesByProductIdentifiers;

    /** @var ValueCollectionFactoryInterface */
    private $valueCollectionFactory;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var GetMetadataInterface */
    private $getMetadata;

    public function __construct(
        GetValuesAndPropertiesFromProductIdentifiers $getValuesAndPropertiesFromProductIdentifiers,
        GetProductAssociationsByProductIdentifiers $getProductAssociationsByProductIdentifiers,
        GetCategoryCodesByProductIdentifiers $getCategoryCodesByProductIdentifiers,
        ValueCollectionFactoryInterface $valueCollectionFactory,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        GetMetadataInterface $getMetadata
    ) {
        $this->getValuesAndPropertiesFromProductIdentifiers = $getValuesAndPropertiesFromProductIdentifiers;
        $this->getProductAssociationsByProductIdentifiers = $getProductAssociationsByProductIdentifiers;
        $this->getCategoryCodesByProductIdentifiers = $getCategoryCodesByProductIdentifiers;
        $this->valueCollectionFactory = $valueCollectionFactory;
        $this->attributeRepository = $attributeRepository;
        $this->getMetadata = $getMetadata;
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

        $associations = [];
        foreach ($this->getProductAssociationsByProductIdentifiers->fetchByProductIdentifiers($identifiers)
                 as $productIdentifier => $productAssociations) {
            $associations[$productIdentifier] = ['associations' => $productAssociations];
        }

        $categoryCodes = [];
        foreach ($this->getCategoryCodesByProductIdentifiers->fetchCategoryCodes($identifiers)
                 as $productIdentifier => $productCategoryCodes) {
            $categoryCodes[$productIdentifier] = ['category_codes' => $productCategoryCodes];
        }

        $metadata = [];
        foreach ($this->getMetadata->fromProductIdentifiers($userId, $identifiers)
                 as $productIdentifier => $workflowStatus) {
            $metadata[$productIdentifier] = ['metadata' => ['workflow_status' => $workflowStatus]];
        }

        $rows = array_replace_recursive(
            $this->getValuesAndPropertiesFromProductIdentifiers->fetchByProductIdentifiers($identifiers),
            $metadata,
            $associations,
            $categoryCodes
        );

        $products = [];
        foreach ($identifiers as $identifier) {
            if (!isset($rows[$identifier])) {
                continue;
            }
            $row = $rows[$identifier];
            $raw_values = $row['raw_values'];

            $raw_values = $this->removeAttribute($raw_values, $identifierAttributeCode);
            if (null !== $attributesToFilterOn) {
                $raw_values = $this->filterWithAttributes($raw_values, $attributesToFilterOn);
            }
            if (null !== $channelToFilterOn) {
                $raw_values = $this->filterWithScope($raw_values, $channelToFilterOn);
            }
            if (null !== $localesToFilterOn) {
                $raw_values = $this->filterWithLocales($raw_values, $localesToFilterOn);
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
                $row['metadata'],
                $this->valueCollectionFactory->createFromStorageFormat($raw_values)
            );
        }

        return new ConnectorProductList($result->count(), $products);
    }

    private function removeAttribute($raw_values, $identifierAttributeCode)
    {
        unset($raw_values[$identifierAttributeCode]);

        return $raw_values;
    }

    private function filterWithAttributes(array $raw_values, array $attributeCodes)
    {
        $result = [];
        foreach ($raw_values as $attributeCode => $attributeValues) {
            if (in_array($attributeCode, $attributeCodes)) {
                $result[$attributeCode] = $attributeValues;
            }
        }

        return $result;
    }

    private function filterWithScope(array $raw_values, $filterScope)
    {
        $result = [];
        foreach ($raw_values as $attributeCode => $attributeValues) {
            foreach ($attributeValues as $scope => $scopedValue) {
                if ($scope === '<all_channels>' || $scope === $filterScope) {
                    $result[$attributeCode][$scope] = $scopedValue;
                }
            }
        }

        return $result;
    }

    private function filterWithLocales(array $raw_values, ?array $localesToFilterOn)
    {
        $result = [];
        foreach ($raw_values as $attributeCode => $attributeValues) {
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
}
