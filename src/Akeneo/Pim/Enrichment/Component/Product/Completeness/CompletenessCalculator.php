<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessCalculator
{
    /** @var GetCompletenessProductMasks */
    private $getCompletenessProductMasks;

    /** @var GetRequiredAttributesMasks */
    private $getRequiredAttributesMasks;

    public function __construct(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetRequiredAttributesMasks $getRequiredAttributesMasks
    ) {
        $this->getCompletenessProductMasks = $getCompletenessProductMasks;
        $this->getRequiredAttributesMasks = $getRequiredAttributesMasks;
    }

    public function fromProductIdentifiers($productIdentifiers): array
    {
        $productMasks = $this->getCompletenessProductMasks->fromProductIdentifiers($productIdentifiers);

        $familyCodes = array_map(function (CompletenessProductMask $product) {
            return $product->familyCode();
        }, $productMasks);

        $requiredAttributesMasks = $this->getRequiredAttributesMasks->fromFamilyCodes(array_unique(array_filter($familyCodes)));

        $result = [];
        foreach ($productMasks as $productMask) {
            $attributeRequirementMask = $requiredAttributesMasks[$productMask->familyCode()] ?? null;
            $result[$productMask->identifier()] = $productMask->completenessCollectionForProduct($attributeRequirementMask);
        }

        return $result;
    }

    public function fromProductIdentifier($productIdentifier): ?ProductCompletenessWithMissingAttributeCodesCollection
    {
        return $this->fromProductIdentifiers([$productIdentifier])[$productIdentifier] ?? null;
    }

    /**
     * Calculates the completeness of an entity with family. It is only useful to calculate missing required attributes
     * for the PEF, and should not be used for any other purpose.
     */
    public function fromEntityWithFamily(
        EntityWithFamilyInterface $entityWithFamily
    ): ProductCompletenessWithMissingAttributeCodesCollection {
        if (null === $entityWithFamily->getFamily()) {
            return new ProductCompletenessWithMissingAttributeCodesCollection($entityWithFamily->getId(), []);
        }
        $familyCode = $entityWithFamily->getFamily()->getCode();
        $requiredAttributesMasks = $this->getRequiredAttributesMasks->fromFamilyCodes([$familyCode]);

        $identifier = $entityWithFamily instanceof ProductModelInterface
            ? $entityWithFamily->getCode()
            : $entityWithFamily->getIdentifier();

        $productMask = $this->getCompletenessProductMasks->fromValueCollection(
            $entityWithFamily->getId(),
            $identifier,
            $familyCode,
            $entityWithFamily->getValues()
        );

        return $productMask->completenessCollectionForProduct($requiredAttributesMasks[$familyCode]);
    }
}
