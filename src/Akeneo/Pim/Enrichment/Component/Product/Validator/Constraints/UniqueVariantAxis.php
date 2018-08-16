<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UniqueVariantAxis extends Constraint
{
    public const DUPLICATE_VALUE_IN_PRODUCT_MODEL = 'pim_catalog.constraint.product_model_with_same_axis_value_already_exists';
    public const DUPLICATE_VALUE_IN_VARIANT_PRODUCT = 'pim_catalog.constraint.variant_product_with_same_axis_value_already_exists';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_unique_variant_axes_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
