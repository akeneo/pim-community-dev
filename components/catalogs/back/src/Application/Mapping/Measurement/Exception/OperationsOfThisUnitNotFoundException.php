<?php

namespace Akeneo\Catalogs\Application\Mapping\Measurement\Exception;

class OperationsOfThisUnitNotFoundException extends \Exception
{
    public function __construct(
        readonly private string $notFoundUnit,
        readonly private string $measurementFamily,
    ) {
        parent::__construct(\sprintf(
            'The Operations of this unit : %s of the measurement family : %s have not been found.',
            $this->notFoundUnit,
            $this->measurementFamily,
        ));
    }
}
