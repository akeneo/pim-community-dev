<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class NotDecimalValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, NotDecimal::class);

        if (!is_numeric($value)) {
            return;
        }
        $numericValue = is_string($value) ? (float) $value : $value;

        if (floor($numericValue) != $numericValue) {
            $this->context->buildViolation($constraint->message, ['{{ invalid_value }}' => $value])->addViolation();
        }
    }
}
