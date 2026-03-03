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
final class EnabledShouldBeValidValidator extends ConstraintValidator
{
    public function validate($condition, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, EnabledShouldBeValid::class);
        if (!\is_array($condition)) {
            return;
        }
        if (!\array_key_exists('type', $condition)) {
            return;
        }
        if ($condition['type'] !== Enabled::type()) {
            return;
        }

        if (!\array_key_exists('value', $condition)) {
            $this->context
                ->buildViolation($constraint->valueKeyRequired)
                ->addViolation();
        } else {
            if (!\is_bool($condition['value'])) {
                $this->context
                    ->buildViolation($constraint->booleanValue)
                    ->atPath('value')
                    ->addViolation();
            }
        }
    }
}
