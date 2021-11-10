<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class ValidationShouldMatchColumnTypeValidator extends ConstraintValidator
{
    private const VALIDATION_MAPPING = [
        'text' => ['max_length', 'required_for_completeness'],
        'number' => ['min', 'max', 'decimals_allowed', 'required_for_completeness'],
        'boolean' => ['required_for_completeness'],
        'select' => ['required_for_completeness'],
    ];

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ValidationShouldMatchColumnType::class);
        if (!\is_array($value)) {
            return;
        }

        $columnType = $value['data_type'] ?? null;
        if (!\array_key_exists($columnType, self::VALIDATION_MAPPING)) {
            return;
        }

        if (!isset($value['validations']) || !\is_array($value['validations']) || [] === $value['validations']) {
            return;
        }

        $validationKeys = \array_keys($value['validations']);
        if ([] === self::VALIDATION_MAPPING[$columnType]) {
            $this->context->buildViolation(
                'pim_table_configuration.validation.table_configuration.no_validations_allowed',
                [
                    '{{ given }}' => \implode(', ', $validationKeys),
                    '{{ columnType }}' => $columnType,
                ]
            )->atPath('validations')->addViolation();

            return;
        }

        $invalidValidationKeys = \array_diff($validationKeys, self::VALIDATION_MAPPING[$columnType]);
        if ([] !== $invalidValidationKeys) {
            $this->context->buildViolation(
                'pim_table_configuration.validation.table_configuration.invalid_validation_key',
                [
                    '{{ expected }}' => \implode(', ', self::VALIDATION_MAPPING[$columnType]),
                    '{{ given }}' => \implode(', ', $invalidValidationKeys),
                    '{{ columnType }}' => $columnType,
                ]
            )->atPath('validations')->addViolation();
        }
    }
}
