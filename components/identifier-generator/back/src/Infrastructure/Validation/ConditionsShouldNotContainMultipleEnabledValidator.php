<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConditionsShouldNotContainMultipleEnabledValidator extends ConstraintValidator
{
    public function validate($conditions, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ConditionsShouldNotContainMultipleEnabled::class);
        if (!\is_array($conditions)) {
            return;
        }

        if (\count(\array_filter($conditions, fn (mixed $condition): bool => \is_array($condition) && \array_key_exists('type', $condition) && $condition['type'] === Enabled::type())) > ConditionsShouldNotContainMultipleEnabled::LIMIT_PER_STRUCTURE) {
            $this->context
                ->buildViolation($constraint->message, [
                    '{{limit}}' => ConditionsShouldNotContainMultipleEnabled::LIMIT_PER_STRUCTURE,
                ])
                ->addViolation();
        }
    }
}
