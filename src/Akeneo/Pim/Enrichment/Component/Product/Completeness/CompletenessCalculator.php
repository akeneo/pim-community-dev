<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;
use Ramsey\Uuid\UuidInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessCalculator
{
    public function __construct(
        private GetCompletenessProductMasks $getCompletenessProductMasks,
        private GetRequiredAttributesMasks $getRequiredAttributesMasks
    ) {
    }

    public function fromProductUuids(array $productUuids): array
    {
        $productMasks = $this->getCompletenessProductMasks->fromProductUuids($productUuids);

        $familyCodes = array_map(function (CompletenessProductMask $product) {
            return $product->familyCode();
        }, $productMasks);

        $requiredAttributesMasks = $this->getRequiredAttributesMasks->fromFamilyCodes(array_unique(array_filter($familyCodes)));

        $result = [];
        foreach ($productMasks as $productMask) {
            $attributeRequirementMask = $requiredAttributesMasks[$productMask->familyCode()] ?? null;
            $result[$productMask->id()] = $productMask->completenessCollectionForProduct($attributeRequirementMask);
        }

        return $result;
    }

    public function fromProductUuid(UuidInterface $productUuid): ?ProductCompletenessWithMissingAttributeCodesCollection
    {
        return $this->fromProductUuids([$productUuid])[$productUuid->toString()] ?? null;
    }
}
