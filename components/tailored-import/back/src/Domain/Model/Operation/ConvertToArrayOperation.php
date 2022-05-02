<?php

namespace Akeneo\Platform\TailoredImport\Domain\Model\Operation;

class ConvertToArrayOperation implements OperationInterface
{
    public const TYPE = 'convert_to_array';

    public function normalize(): array
    {
        return [
            'type' => self::TYPE,
        ];
    }
}
