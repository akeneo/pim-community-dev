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
        'text' => ['max_length'],
        'number' => ['min', 'max', 'decimals_allowed'],
        'boolean' => [],
        'select' => [],
    ];

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ValidationShouldMatchColumnType::class);
        if (!\is_array($value)) {
            return;
        }

        $columnType = $value['data_type'] ?? null;
        if (!\in_array($columnType, \array_keys(self::VALIDATION_MAPPING), true)) {
            return;
        }

        if (!isset($value['validations']) || !\is_array($value['validations']) || [] === $value['validations']) {
            return;
        }

        $validationKeys = \array_keys($value['validations']);
        if ([] === self::VALIDATION_MAPPING[$columnType]) {
            $this->context->buildViolation(
                'Do not use Validations on a "{{ columnType }}" column, current column type: "{{ given }}"',
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
                'The "{{ given }}" Validations does not work on a "{{ columnType }}" column, allowed: "{{ expected }}"',
                [
                    '{{ expected }}' => \implode(', ', self::VALIDATION_MAPPING[$columnType]),
                    '{{ given }}' => \implode(', ', $invalidValidationKeys),
                    '{{ columnType }}' => $columnType,
                ]
            )->atPath('validations')->addViolation();
        }
    }
}
