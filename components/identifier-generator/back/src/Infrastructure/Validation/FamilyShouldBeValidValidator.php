<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Family;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuery;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyCodes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FamilyShouldBeValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly FindFamilyCodes $findFamilyCodes,
        private readonly ValidatorInterface $validator,
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

        $this->validateConditionKeys($condition, $constraint);

        if (!\array_key_exists('operator', $condition)) {
            return;
        }

        if (\in_array($condition['operator'], ['EMPTY', 'NOT EMPTY'])) {
            $this->validateValueIsUndefined($condition);
        } else {
            $this->validateValueField($condition);

            if (!\array_key_exists('value', $condition)) {
                return;
            }
            if (!\is_array($condition['value'])) {
                return;
            }
            foreach ($condition['value'] as $value) {
                if (!\is_string($value)) {
                    return;
                }
            }

            $this->validateFamiliesExist($condition['value'], $constraint);
        }
    }

    private function validateConditionKeys(array $condition, FamilyShouldBeValid $constraint): void
    {
        $this->validator->inContext($this->context)->validate($condition, new Collection([
            'type' => null,
            'operator' => new Choice(
                choices: ['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY'],
                message: $constraint->unknownOperator
            ),
            'value' => [new Optional()],
        ]));
    }

    private function validateValueIsUndefined(array $condition): void
    {
        $this->validator->inContext($this->context)->validate($condition, new Collection([
            'type' => null,
            'operator' => null,
        ]));
    }

    private function validateValueField(array $condition): void
    {
        $this->validator->inContext($this->context)->validate($condition, new Collection([
            'type' => null,
            'operator' => null,
            'value' => [
                new Count(
                    min: 1
                ),
                new All([
                    new Type('string'),
                    new NotBlank(),
                ])
            ]
        ]));
    }

    /**
     * @param string[] $familyCodes
     */
    private function validateFamiliesExist(array $familyCodes, FamilyShouldBeValid $constraint): void
    {
        $existingCodes = $this->findFamilyCodes->fromQuery(new FamilyQuery(includeCodes: $familyCodes));
        $nonExistingCodes = \array_diff($familyCodes, $existingCodes);
        if (\count($nonExistingCodes) > 0) {
            $this->context
                ->buildViolation($constraint->familyNotExist, [ '{{ familyCodes }}' =>  \implode(', ', \array_map(fn(string $value): string => \json_encode($value), $nonExistingCodes))])
                ->atPath('value')
                ->addViolation();
        }
    }
}
