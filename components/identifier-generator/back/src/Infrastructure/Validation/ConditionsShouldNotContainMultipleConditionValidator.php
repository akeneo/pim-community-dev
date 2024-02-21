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
final class ConditionsShouldNotContainMultipleConditionValidator extends ConstraintValidator
{
    public function validate($conditions, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ConditionsShouldNotContainMultipleCondition::class);
        if (!\is_array($conditions)) {
            return;
        }

        foreach ($constraint->types as $type) {
            if (\count(\array_filter($conditions, fn (mixed $condition): bool => \is_array($condition) && \array_key_exists('type', $condition) && $condition['type'] === $type)) > 1) {
                $this->context
                    ->buildViolation($constraint->message, [
                        '{{limit}}' => 1,
                        '{{type}}' => $type,
                    ])
                    ->addViolation();
            }
        }
    }
}
