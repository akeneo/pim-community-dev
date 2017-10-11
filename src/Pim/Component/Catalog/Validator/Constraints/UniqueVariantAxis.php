<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UniqueVariantAxis extends Constraint
{
    public const DUPLICATE_VALUE_IN_SIBLING = 'pim_catalog.constraint.can_have_family_variant_duplicate_axis_value';

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
