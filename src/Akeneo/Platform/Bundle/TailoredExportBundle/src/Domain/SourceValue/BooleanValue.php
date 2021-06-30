<?php

namespace Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\SourceValue;

use Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain\SourceValue;

class BooleanValue implements SourceValue
{
    private bool $data;

    public function __construct(bool $data)
    {
        $this->data = $data;
    }

    public function getData(): bool
    {
        return $this->data;
    }
}