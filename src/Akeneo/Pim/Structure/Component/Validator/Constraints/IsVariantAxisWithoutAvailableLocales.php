<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsVariantAxisWithoutAvailableLocales extends Constraint
{
    public string $message = 'pim_structure.validation.available_locales.is_variant_axis_without_available_locales';
    public string $propertyPath = 'available_locales';

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pim_structure.validator.constraint.available_locales.is_variant_axis_without_available_locales';
    }
}
