<?php

namespace Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\SourceValue;

use Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\SourceValue;

class StringValue implements SourceValue
{
    private string $data;

    public function __construct(string $data)
    {
        $this->data = $data;
    }

    public function getData(): string
    {
        return $this->data;
    }
}