<?php

namespace Akeneo\Platform\Syndication\Domain\Model;

class PlatformFamily
{
    public function __construct(
        private string $code,
        private string $platformCode,
        private string $label,
        private array $requirements
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getPlatformCode(): string
    {
        return $this->platformCode;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getRequirements(): array
    {
        return $this->requirements;
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code,
            'connected_channel_code' => $this->platformCode,
            'label' => $this->label,
            'requirements' => $this->requirements,
        ];
    }
}
