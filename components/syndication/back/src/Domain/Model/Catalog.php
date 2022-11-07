<?php

namespace Akeneo\Platform\Syndication\Domain\Model;

class Catalog
{
    public function __construct(
        private string $uuid,
        private string $code,
        private string $label
    ) {
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function normalize(): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'label' => $this->label
        ];
    }
}
