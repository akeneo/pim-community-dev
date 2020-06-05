<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\Operand;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\Operation;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\OperandKeys;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class OperandKeysValidator extends ConstraintValidator
{
    public function validate($operand, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, OperandKeys::class);
        Assert::isInstanceOfAny($operand, [Operand::class, Operation::class]);

        if (null === $operand->value && null === $operand->field) {
            $this->context->buildViolation($constraint->requiredKeyMessage)->addViolation();

            return;
        }
        if (null !== $operand->value && null !== $operand->field) {
            $this->context->buildViolation($constraint->onlyOneKeyExpectedKeyMessage)->addViolation();

            return;
        }

        if (null === $operand->value) {
            return;
        }

        if (null !== $operand->scope) {
            $this->context->buildViolation(
                $constraint->unexpectedKeyMessage,
                ['{{ key }}' => 'scope']
            )->addViolation();
        }
        if (null !== $operand->locale) {
            $this->context->buildViolation(
                $constraint->unexpectedKeyMessage,
                ['{{ key }}' => 'locale']
            )->addViolation();
        }
        if (null !== $operand->currency) {
            $this->context->buildViolation(
                $constraint->unexpectedKeyMessage,
                ['{{ key }}' => 'currency']
            )->addViolation();
        }
    }
}
