<?php

namespace Akeneo\Catalogs\Application\Mapping\Measurement\Exception;

class UseOfUnknownOperatorException extends \Exception
{
    public function __construct(
        readonly private string $unknownOperator,
    ) {
        parent::__construct(\sprintf(
            'The operator : %s used for this operation is not listed in the configured operator for this measurement unit.',
            $this->unknownOperator,
        ));
    }
}
