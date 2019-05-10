<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Connector;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\ProductModel\Query\Sql\GetCategoryCodesByProductModelCodes;
use Akeneo\Pim\Enrichment\Bundle\ProductModel\Query\Sql\GetGroupAssociationsByProductModelCodes;
use Akeneo\Pim\Enrichment\Bundle\ProductModel\Query\Sql\GetProductAssociationsByProductModelCodes;
use Akeneo\Pim\Enrichment\Bundle\ProductModel\Query\Sql\GetProductModelsAssociationsByProductModelCodes;
use Akeneo\Pim\Enrichment\Bundle\ProductModel\Query\Sql\GetValuesAndPropertiesFromProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetConnectorProductModels implements Query\GetConnectorProductModels
{
    /** @var GetValuesAndPropertiesFromProductModelCodes */
    private $getValuesAndPropertiesFromProductModelCodes;

    /** @var GetCategoryCodesByProductModelCodes */
    private $getCategoryCodesByProductModelCodes;

    /** @var GetProductAssociationsByProductModelCodes */
    private $getProductAssociationsByProductModelCodes;

    /** @var GetProductModelsAssociationsByProductModelCodes */
    private $getProductModelAssociationsByProductModelCodes;

    /** @var GetGroupAssociationsByProductModelCodes */
    private $getGroupAssociationsByProductModelCodes;

    /** @var ValueCollectionFactoryInterface */
    private $valueCollectionFactory;

    public function __construct(
        GetValuesAndPropertiesFromProductModelCodes $getValuesAndPropertiesFromProductModelCodes,
        GetCategoryCodesByProductModelCodes $getCategoryCodesByProductModelCodes,
        GetProductAssociationsByProductModelCodes $getProductAssociationsByProductModelCodes,
        GetProductModelsAssociationsByProductModelCodes $getProductModelAssociationsByProductModelCodes,
        GetGroupAssociationsByProductModelCodes $getGroupAssociationsByProductModelCodes,
        ValueCollectionFactoryInterface $valueCollectionFactory
    ) {
        $this->getValuesAndPropertiesFromProductModelCodes = $getValuesAndPropertiesFromProductModelCodes;
        $this->getCategoryCodesByProductModelCodes = $getCategoryCodesByProductModelCodes;
        $this->getProductAssociationsByProductModelCodes = $getProductAssociationsByProductModelCodes;
        $this->getProductModelAssociationsByProductModelCodes = $getProductModelAssociationsByProductModelCodes;
        $this->getGroupAssociationsByProductModelCodes = $getGroupAssociationsByProductModelCodes;
        $this->valueCollectionFactory = $valueCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductQueryBuilder(
        ProductQueryBuilderInterface $productQueryBuilder,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductModelList {
        $result = $productQueryBuilder->execute();
        $productModelCodes = array_map(
            function (IdentifierResult $identifier) {
                return $identifier->getIdentifier();
            },
            iterator_to_array($result)
        );

        $connectorProductModels = $this->fromProductModelCodes(
            $productModelCodes,
            $attributesToFilterOn,
            $channelToFilterOn,
            $localesToFilterOn
        );

        return new ConnectorProductModelList($result->count(), $connectorProductModels);
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductModelCode(string $productModelCode, int $userId): ConnectorProductModel
    {
        $connectorProductModels = $this->fromProductModelCodes([$productModelCode], null, null, null);

        if (empty($connectorProductModels)) {
            throw new ObjectNotFoundException(sprintf('Product model "%s" was not found', $productModelCode));
        }

        return $connectorProductModels[0];
    }

    private function fromProductModelCodes(
        array $productModelCodes,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): array {
        $rows = array_replace_recursive(
            $this->getValuesAndPropertiesFromProductModelCodes->fromProductModelCodes($productModelCodes),
            $this->fetchAssociationsIndexedByProductModelCode($productModelCodes),
            $this->fetchCategoryCodesIndexedByProductModelCode($productModelCodes)
        );
        $productModels = [];
        foreach ($productModelCodes as $productModelCode) {
            if (!isset($rows[$productModelCode]['code'])) {
                continue;
            }
            $row = $rows[$productModelCode];
            $rawValues = $row['raw_values'];
            if (null !== $attributesToFilterOn) {
                $rawValues = $this->filterOnAttributeCodes($rawValues, $attributesToFilterOn);
            }
            if (null !== $channelToFilterOn) {
                $rawValues = $this->filterOnChannelCode($rawValues, $channelToFilterOn);
            }
            if (null !== $localesToFilterOn) {
                $rawValues = $this->filterOnLocaleCodes($rawValues, $localesToFilterOn);
            }
            $productModels[] = new ConnectorProductModel(
                $row['id'],
                $row['code'],
                $row['created'],
                $row['updated'],
                $row['parent'],
                $row['family_variant'],
                [],
                $row['associations'],
                $row['category_codes'],
                $this->valueCollectionFactory->createFromStorageFormat($rawValues)
            );
        }

        return $productModels;
    }

    private function fetchCategoryCodesIndexedByProductModelCode(array $productModelCodes): array
    {
        $categoryCodes = [];
        foreach ($this->getCategoryCodesByProductModelCodes->fromProductModelCodes(
            $productModelCodes
        ) as $productModelCode => $productModelCategoryCodes) {
            $categoryCodes[$productModelCode] = ['category_codes' => $productModelCategoryCodes];
        }

        return $categoryCodes;
    }

    private function fetchAssociationsIndexedByProductModelCode(array $productModelCodes): array
    {
        $associations = array_replace_recursive(
            $this->getProductAssociationsByProductModelCodes->fetchByProductModelCodes($productModelCodes),
            $this->getProductModelAssociationsByProductModelCodes->fromProductModelCodes($productModelCodes),
            $this->getGroupAssociationsByProductModelCodes->fromProductModelCodes($productModelCodes)
        );
        $associationsIndexedByCode = [];
        foreach ($associations as $productModelCode => $association) {
            $associationsIndexedByCode[$productModelCode]['associations'] = $association;
        }

        return $associationsIndexedByCode;
    }

    private function filterOnAttributeCodes(array $rawValues, array $attributeCodes): array
    {
        $result = [];
        foreach ($rawValues as $attributeCode => $attributeValues) {
            if (in_array($attributeCode, $attributeCodes)) {
                $result[$attributeCode] = $attributeValues;
            }
        }

        return $result;
    }

    private function filterOnChannelCode(array $rawValues, string $filterScope): array
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

    private function filterOnLocaleCodes(array $rawValues, array $localesToFilterOn): array
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
}
