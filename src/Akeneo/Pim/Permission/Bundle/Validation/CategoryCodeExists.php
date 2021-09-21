<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Validation;

use Symfony\Component\Validator\Constraint;

class CategoryCodeExists extends Constraint
{
    public string $message = 'Category code must exists';

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
