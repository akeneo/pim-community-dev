<?php

namespace Akeneo\Catalogs\ServiceAPI\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[\Attribute]
class MaxNumberOfCatalogsPerUser extends Constraint
{
    public string $message = 'akeneo_catalogs.validation.max_number_of_catalogs_per_user_message';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
