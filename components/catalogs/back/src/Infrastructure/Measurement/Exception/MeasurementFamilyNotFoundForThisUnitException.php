<?php

namespace Akeneo\Catalogs\Infrastructure\Measurement\Exception;

class MeasurementFamilyNotFoundForThisUnitException extends \Exception
{
    public function __construct(
        private readonly string $unitCode,
    ) {
        parent::__construct(\sprintf(
            'The measurement family of this unit : %s have not been found.',
            $this->unitCode,
        ));
    }
}
