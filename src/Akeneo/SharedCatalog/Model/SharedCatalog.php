<?php

namespace Akeneo\SharedCatalog\Model;

class SharedCatalog
{
    /** @var string */
    private $code;
    /** @var string|null */
    private $publisher;
    /** @var array|null */
    private $recipients;
    /** @var array|null */
    private $filters;
    /** @var array|null */
    private $branding;
    /** @var string */
    private $label;

    public function __construct(
        string $code,
        string $label,
        ?string $publisher,
        array $recipients,
        ?array $filters,
        ?array $branding
    ) {
        $this->code = $code;
        $this->label = $label;
        $this->publisher = $publisher;
        $this->recipients = $recipients;
        $this->filters = $filters;
        $this->branding = $branding;
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
            'recipients' => array_map(function ($recipient) {
                return $recipient['email'];
            }, $this->recipients ?? []),
            'channel' => $this->filters['structure']['scope'] ?? null,
            'catalogLocales' => $this->filters['structure']['locales'] ?? [],
            'attributes' => $this->filters['structure']['attributes'] ?? [],
            'branding' => [
                'logo' => $this->branding['image'] ?? null,
                'cover_image' => $this->branding['cover_image'] ?? null,
                'color' => $this->branding['color'] ?? null,
            ],
        ];
    }
}
