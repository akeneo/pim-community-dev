<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FamilyShouldBeValidValidator extends ConstraintValidator
{
    public function validate($condition, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, FamilyShouldBeValid::class);

        if (!\is_array($condition)) {
            return;
        }

        if (!\array_key_exists('operator', $condition)) {
            return;
        }

        if (!\in_array($condition['operator'], ['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY'])) {
            $this->context
                ->buildViolation($constraint->unknownOperator)
                ->atPath('operator')
                ->setParameters([
                    '{{ value }}' => \json_encode($condition['operator']),
                    '{{ choices }}' => \implode(', ', \array_map(fn (string $value): string => \json_encode($value), ['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY']))
                ])
                ->addViolation();
        }

        if (\in_array($condition['operator'], ['EMPTY', 'NOT EMPTY']) && \array_key_exists('value', $condition)) {
            $this->context
                ->buildViolation($constraint->valueFilled)
                ->atPath('value')
                ->addViolation();
        }

        if (\in_array($condition['operator'], ['IN', 'NOT IN']) && \array_key_exists('value', $condition) && !\is_array($condition['value'])) {
            $this->context
                ->buildViolation($constraint->valueShouldBeAnArray)
                ->atPath('value')
                ->addViolation();
        }

        if (\in_array($condition['operator'], ['IN', 'NOT IN']) && !\array_key_exists('value', $condition)) {
            $this->context
                ->buildViolation($constraint->valueShouldBePresent)
                ->addViolation();
        }

        if (\in_array($condition['operator'], ['IN', 'NOT IN']) && \array_key_exists('value', $condition) && \is_array($condition['value'])) {
            if (\count($condition['value']) === 0) {
                $this->context
                    ->buildViolation($constraint->valueShouldNotBeBlank)
                    ->atPath('value')
                    ->addViolation();
            }

            foreach ($condition['value'] as $value) {
                if (!\is_string($value)) {
                    $this->context
                        ->buildViolation($constraint->valueShouldBeAnArray)
                        ->atPath('value')
                        ->addViolation();
                }
            }
        }
    }
}
