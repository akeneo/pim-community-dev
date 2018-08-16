<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validates that the variant axis values cannot be modified once set.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ImmutableVariantAxesValues extends Constraint
{
    public const UPDATED_VARIANT_AXIS_VALUE = 'pim_catalog.constraint.modified_variant_axis_value';

    /** @var string */
    public $propertyPath = 'attribute';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_immutable_variant_axis_values_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
