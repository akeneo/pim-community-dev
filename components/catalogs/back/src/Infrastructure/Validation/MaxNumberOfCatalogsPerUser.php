<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[\Attribute]
final class MaxNumberOfCatalogsPerUser extends Constraint
{
    public string $message = 'akeneo_catalogs.validation.max_number_of_catalogs_per_user_message';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
