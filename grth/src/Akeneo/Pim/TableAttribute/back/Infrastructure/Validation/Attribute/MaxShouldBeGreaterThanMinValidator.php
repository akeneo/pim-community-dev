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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class MaxShouldBeGreaterThanMinValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, MaxShouldBeGreaterThanMin::class);
        if (!\is_array($value)) {
            return;
        }
        if (\is_int($value['min'] ?? null) && \is_int($value['max'] ?? null) && $value['min'] > $value['max']) {
            $this->context->buildViolation('pim_table_configuration.validation.table_configuration.max_should_be_greater_than_min', [])->addViolation();
        }
    }
}
