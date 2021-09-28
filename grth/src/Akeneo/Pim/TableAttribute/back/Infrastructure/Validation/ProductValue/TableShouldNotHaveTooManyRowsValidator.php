<?php

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class TableShouldNotHaveTooManyRowsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, TableShouldNotHaveTooManyRows::class);

        if (!$value instanceof TableValue) {
            return;
        }

        if (count($value->getData()) > TableShouldNotHaveTooManyRows::LIMIT) {
            $this
                ->context
                ->buildViolation($constraint->message, ['{{ limit }}' => TableShouldNotHaveTooManyRows::LIMIT])
                ->addViolation();
        }
    }
}
