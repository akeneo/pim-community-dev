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

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\Operation;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operation as OperationModel;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\DivisionByZero;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class DivisionByZeroValidator extends ConstraintValidator
{
    public function validate($operation, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, DivisionByZero::class);
        Assert::isInstanceOf($operation, Operation::class);

        if (0.0 === $operation->value && OperationModel::DIVIDE === $operation->operator) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
