<?php

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Measurement;

use Symfony\Component\Validator\Constraint;

class MeasurementSourceParameter extends Constraint
{
    public function __construct(
        private string $familyCode
    ) {
        parent::__construct();
    }

    public function getFamilyCode(): string
    {
        return $this->familyCode;
    }
}
