<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Component\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class LocaleCode extends Constraint
{
    public $message = 'pim_enrich.entity.locale.constraint.invalid_locale_code';

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'pim_locale_code_validator';
    }
}
