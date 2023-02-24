<?php

namespace Akeneo\Catalogs\Infrastructure\Measurement\Exception;

class UnitNotFoundException extends \Exception
{
    public function __construct(
        readonly private string $notFoundUnit,
        readonly private string $measurementFamily,
    ) {
        parent::__construct(\sprintf(
            'This unit : %s of the measurement family : %s have not been found.',
            $this->notFoundUnit,
            $this->measurementFamily,
        ));
    }
}
