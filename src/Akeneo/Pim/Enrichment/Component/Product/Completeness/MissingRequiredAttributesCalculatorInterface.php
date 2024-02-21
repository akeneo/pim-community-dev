<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;

/**
 * This class calculates a pseudo-completeness based on the current values of an entity with family. It is only useful
 * to compute the missing required attributes for the PEF (e.g for a product model), and SHOULD NOT be used for any
 * other purpose
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MissingRequiredAttributesCalculatorInterface
{
    /**
     * Calculates the completeness of an entity with family. It is only useful to calculate missing required attributes
     * for the PEF, and should not be used for any other purpose.
     */
    public function fromEntityWithFamily(
        EntityWithFamilyInterface $entityWithFamily
    ): ProductCompletenessWithMissingAttributeCodesCollection;
}
