<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;

/**
 * This class calculates a pseudo-completeness based on the current values of an entity with family. It is only useful
 * to compute the missing required attributes for the PEF (e.g for a product model), and SHOULD NOT be used for any
 * other purpose
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MissingRequiredAttributesCalculator
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

        $identifier = $entityWithFamily instanceof ProductInterface ?
            $entityWithFamily->getIdentifier() :
            $entityWithFamily->getCode();

        $productMask = $this->getCompletenessProductMasks->fromValueCollection(
            $entityWithFamily->getId(),
            $identifier,
            $familyCode,
            $entityWithFamily->getValues()
        );

        return $productMask->completenessCollectionForProduct($requiredAttributesMasks[$familyCode]);
    }
}
