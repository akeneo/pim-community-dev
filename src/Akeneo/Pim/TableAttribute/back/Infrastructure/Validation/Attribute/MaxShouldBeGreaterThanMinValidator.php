<?php

declare(strict_types=1);

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
            $this->context->buildViolation('TODO max should be greater than min', [])->addViolation();
        }
    }
}
