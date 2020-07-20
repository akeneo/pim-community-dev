<?php

namespace Akeneo\SharedCatalog\Model;

final class SharedCatalog
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

    public function __construct(
        string $code,
        ?string $publisher,
        array $recipients,
        ?array $filters,
        ?array $branding
    ) {
        $this->code = $code;
        $this->publisher = $publisher;
        $this->recipients = $recipients;
        $this->filters = $filters;
        $this->branding = $branding;
    }

    public function normalizeForExternalApi(): array
    {
        return [
            'code' => $this->code,
            'publisher' => $this->publisher,
            'recipients' => array_map(function ($recipient) {
                return $recipient['email'];
            }, $this->recipients ?? []),
            'channel' => $this->filters['structure']['scope'] ?? null,
            'catalogLocales' => $this->filters['structure']['locales'] ?? [],
            'attributes' => $this->filters['structure']['attributes'] ?? [],
            'branding' => [
                'logo' => $this->branding['image'] ?? null,
            ],
        ];
    }
}
