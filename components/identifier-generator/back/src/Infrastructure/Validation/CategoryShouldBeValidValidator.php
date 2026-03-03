<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Category;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\CategoryOperator;
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
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CategoryShouldBeValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function validate($condition, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, CategoryShouldBeValid::class);

        if (!\is_array($condition)) {
            return;
        }

        if (\array_key_exists('type', $condition) && $condition['type'] !== Category::type()) {
            return;
        }

        $this->validateConditionKeys($condition, $constraint);

        if (!\array_key_exists('operator', $condition)) {
            return;
        }

        if (\in_array($condition['operator'], [CategoryOperator::CLASSIFIED->value, CategoryOperator::UNCLASSIFIED->value])) {
            $this->validateValueIsUndefined($condition);
        } else {
            $this->validateValueField($condition);
        }
    }

    /**
     * @param array<string, mixed> $condition
     */
    private function validateConditionKeys(array $condition, CategoryShouldBeValid $constraint): void
    {
        $this->validator->inContext($this->context)->validate($condition, new Collection([
            'type' => null,
            'operator' => new Choice(
                choices: \array_column(CategoryOperator::cases(), 'value'),
                message: $constraint->unknownOperator
            ),
            'value' => [new Optional()],
        ]));
    }

    /**
     * @param array<string, mixed> $condition
     */
    private function validateValueIsUndefined(array $condition): void
    {
        $this->validator->inContext($this->context)->validate($condition, new Collection([
            'type' => null,
            'operator' => null,
        ]));
    }

    /**
     * @param array<string, mixed> $condition
     */
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
                ]),
                new CategoryCodesShouldExist(),
            ],
        ]));
    }
}
