<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductModelProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductModelProjection;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetValuesAndPropertiesFromProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CompleteFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetElasticsearchProductModelProjection implements GetElasticsearchProductModelProjectionInterface
{
    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var CompleteFilterInterface */
    private $completenessGridFilterQuery;

    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributesProvider;

    /** @var GetValuesAndPropertiesFromProductModelCodes */
    private $getValuesAndPropertiesFromProductModelCodes;

    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        CompleteFilterInterface $completenessGridFilterQuery,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        GetValuesAndPropertiesFromProductModelCodes $getValuesAndPropertiesFromProductModelCodes
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->completenessGridFilterQuery = $completenessGridFilterQuery;
        $this->attributesProvider = $attributesProvider;
        $this->getValuesAndPropertiesFromProductModelCodes = $getValuesAndPropertiesFromProductModelCodes;
    }

    public function fromProductModelCodes(array $productModelCodes): array
    {
        $valuesAndProperties = $this
            ->getValuesAndPropertiesFromProductModelCodes
            ->fetchByProductModelCodes($productModelCodes);
        $productProjections = [];

        foreach ($productModelCodes as $productModelCode) {
            $productModel = $this->productModelRepository->findOneByIdentifier($productModelCode);
            $normalizedData = $this->completenessGridFilterQuery->findCompleteFilterData($productModel);

            $productProjections[$productModelCode] = new ElasticsearchProductModelProjection(
                $valuesAndProperties[$productModelCode]['id'],
                $valuesAndProperties[$productModelCode]['code'],
                $valuesAndProperties[$productModelCode]['created'],
                $valuesAndProperties[$productModelCode]['updated'],
                $valuesAndProperties[$productModelCode]['family_code'],
                $valuesAndProperties[$productModelCode]['family_labels'],
                $valuesAndProperties[$productModelCode]['family_variant_code'],
                $valuesAndProperties[$productModelCode]['category_codes'],
                $valuesAndProperties[$productModelCode]['ancestor_category_codes'],
                $valuesAndProperties[$productModelCode]['parent_code'],
                $valuesAndProperties[$productModelCode]['values'],
                $normalizedData->allComplete(),
                $normalizedData->allIncomplete(),
                $valuesAndProperties[$productModelCode]['ancestor_ids'],
                $valuesAndProperties[$productModelCode]['ancestor_codes'],
                $valuesAndProperties[$productModelCode]['ancestor_labels'],
                $valuesAndProperties[$productModelCode]['labels'],
                $this->getAttributesOfAncestors($productModel),
                $this->getSortedAttributeCodes($productModel)
            );
        }

        return $productProjections;
    }

    private function getSortedAttributeCodes(ProductModelInterface $entityWithFamilyVariant): array
    {
        $attributes = $this->attributesProvider->getAttributes($entityWithFamilyVariant);
        $attributeCodes = array_map(function (AttributeInterface $attribute) {
            return $attribute->getCode();
        }, $attributes);

        sort($attributeCodes);

        return $attributeCodes;
    }

    private function getAttributesOfAncestors(ProductModelInterface $productModel): array
    {
        if (null === $productModel->getFamilyVariant()) {
            return [];
        }
        if (ProductModel::ROOT_VARIATION_LEVEL === $productModel->getVariationLevel()) {
            return [];
        }
        $attributesOfAncestors = $productModel->getFamilyVariant()
            ->getCommonAttributes()
            ->map(
                function (AttributeInterface $attribute) {
                    return $attribute->getCode();
                }
            )->toArray();
        sort($attributesOfAncestors);

        return $attributesOfAncestors;
    }
}
