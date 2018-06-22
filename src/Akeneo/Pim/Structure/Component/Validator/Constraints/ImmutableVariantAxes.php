<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validates that the variant axis cannot be modified once set.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImmutableVariantAxes extends Constraint
{
    public const IMMUTABLE_VARIANT_AXES = 'pim_catalog.constraint.family_variant_axes_immutable';

    /** @var string */
    public $propertyPath = 'axes';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_immutable_variant_axes_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
