<?php

namespace Akeneo\SharedCatalog\Model;

class SharedCatalog
{
    public const DEFAULT_COLOR = '#f9b53f';

    public function __construct(
        private  string $code,
        private string $label,
        private ?string $publisher,
        private array $recipients,
        private ?array $filters,
        private ?array $branding
    ) {
    }

    public function getDefaultScope(): ?string
    {
        return $this->filters['structure']['scope'] ?? null;
    }

    public function getPQBFilters(): array
    {
        return (array)($this->filters['data'] ?? []);
    }

    public function normalizeForExternalApi(): array
    {
        return [
            'code' => $this->code,
            'label' => $this->label,
            'publisher' => $this->publisher,
            'recipients' => array_map(static fn ($recipient) => $recipient['email'], $this->recipients ?? []),
            'channel' => $this->filters['structure']['scope'] ?? null,
            'catalogLocales' => $this->filters['structure']['locales'] ?? [],
            'attributes' => $this->filters['structure']['attributes'] ?? [],
            'branding' => [
                'logo' => $this->branding['image'] ?? null,
                'cover_image' => $this->branding['cover_image'] ?? null,
                'color' => $this->branding['color'] ?? self::DEFAULT_COLOR,
            ],
        ];
    }
}
