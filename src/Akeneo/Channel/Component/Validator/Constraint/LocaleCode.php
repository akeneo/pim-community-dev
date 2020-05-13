<?php

declare(strict_types=1);

namespace Akeneo\Channel\Component\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class LocaleCode extends Constraint
{
    public $message = 'pim_enrich.entity.locale.constraint.invalid_locale_code';

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'pim_locale_code_validator';
    }
}
