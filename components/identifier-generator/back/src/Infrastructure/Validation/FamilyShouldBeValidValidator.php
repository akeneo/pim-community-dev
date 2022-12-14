<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Family;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuery;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyCodes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FamilyShouldBeValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly FindFamilyCodes $findFamilyCodes,
    ) {
    }

    public function validate($condition, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, FamilyShouldBeValid::class);

        if (!\is_array($condition)) {
            return;
        }

        if (\array_key_exists('type', $condition) && $condition['type'] !== Family::type()) {
            return;
        }

        if (!\array_key_exists('operator', $condition)) {
            $this->context
                ->buildViolation($constraint->operatorShouldBePresent)
                ->addViolation();
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
                return;
            }

            foreach ($condition['value'] as $value) {
                if (!\is_string($value)) {
                    $this->context
                        ->buildViolation($constraint->valueShouldBeAnArray)
                        ->atPath('value')
                        ->addViolation();
                    return;
                }
            }

            $existingCodes = $this->findFamilyCodes->fromQuery(new FamilyQuery(includeCodes: $condition['value']));
            $nonExistingCodes = \array_diff($condition['value'], $existingCodes);
            if (\count($nonExistingCodes) > 0) {
                $this->context
                    ->buildViolation($constraint->familyNotExist)
                    ->atPath('value')
                    ->setParameter('{{ familyCodes }}', \implode(', ', \array_map(fn (string $value): string => \json_encode($value), $nonExistingCodes)))
                    ->addViolation();
            }
        }
    }
}
