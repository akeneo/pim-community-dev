<?php

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Measurement;

use Symfony\Component\Validator\Constraint;

final class Unit extends Constraint
{
    public const UNIT_SHOULD_EXIST = 'akeneo.tailored_import.validation.target.source_configuration.unit_should_exist';

    public function __construct(
        private string $familyCode,
    ) {
        parent::__construct();
    }

    public function getFamilyCode(): string
    {
        return $this->familyCode;
    }
}
