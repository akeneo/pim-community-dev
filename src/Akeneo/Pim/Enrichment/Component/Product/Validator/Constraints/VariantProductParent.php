<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check the parent of a variant product.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class VariantProductParent extends Constraint
{
    public const NO_PARENT = 'pim_catalog.constraint.variant_product_has_parent';
    public const INVALID_PARENT = 'pim_catalog.constraint.invalid_variant_product_parent';

    /** @var string */
    public $propertyPath = 'parent';


    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pim_invalid_variant_product_parent';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
