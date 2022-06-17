<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[\Attribute]
class CriteriaJson extends Constraint
{
    public string $message = 'akeneo_catalogs.validation.criteria_json';
}
