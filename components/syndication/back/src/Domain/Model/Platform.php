<?php

namespace Akeneo\Platform\Syndication\Domain\Model;

class Platform
{
    public function __construct(
        private string $code,
        private string $label,
        private bool $enabled,
        private array $families
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getFamilies(): array
    {
        return $this->families;
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code,
            'label' => $this->label,
            'enabled' => $this->enabled,
            'families' => $this->families,
        ];
    }
}
