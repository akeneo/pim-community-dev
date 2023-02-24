<?php

namespace Akeneo\Catalogs\Infrastructure\Measurement\Exception;

class MeasurementFamilyNotFoundException extends \Exception
{
    public function __construct(
        private readonly string $measurementFamilyCode,
    ) {
        parent::__construct(\sprintf(
            'The measurement family with this code : %s have not been found.',
            $this->measurementFamilyCode,
        ));
    }
}
