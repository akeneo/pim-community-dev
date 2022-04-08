<?php

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Measurement;

use Symfony\Component\Validator\Constraint;

class Unit extends Constraint
{
    public const UNIT_SHOULD_EXIST = 'akeneo.tailored_import.validation.target.source_parameter.unit_should_exist';

    public function __construct(
        private string $familyCode
    ) {
    }

    public function getFamilyCode(): string
    {
        return $this->familyCode;
    }
}
