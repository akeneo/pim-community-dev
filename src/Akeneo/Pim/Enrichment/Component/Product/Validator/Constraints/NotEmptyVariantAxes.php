<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class NotEmptyVariantAxes extends Constraint
{
    public const EMPTY_AXIS_VALUE = 'pim_catalog.constraint.can_have_family_variant_empty_axis_value';

    /** @var string */
    public $propertyPath = 'attribute';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_not_empty_axes_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
