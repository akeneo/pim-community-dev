<?php

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class MaxLimitPerUser extends Constraint
{
    public string $message = 'You can create up to {{ number }} catalogs per app';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
