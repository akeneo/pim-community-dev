<?php

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class MaxLimit extends Constraint
{
    public string $message = 'You can create up to 15 catalogs per app';
}
