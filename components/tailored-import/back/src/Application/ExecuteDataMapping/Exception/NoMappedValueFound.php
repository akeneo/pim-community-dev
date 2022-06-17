<?php

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception;

class NoMappedValueFound extends ApplyOperationException
{
    private const MESSAGE = 'No mapped value for this source value : %s';

    public function __construct(mixed $invalidValue)
    {
        parent::__construct(sprintf(self::MESSAGE, $invalidValue));
    }
}
