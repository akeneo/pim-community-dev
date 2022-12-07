<?php

namespace Akeneo\Platform\Syndication\Domain\Model;

class PlatformConfiguration
{
    public function __construct(
        private string $code,
        private string $label,
        private array  $catalogs, // Should be improved and be properly typed
        private string $syndicationChannel // Should be improved and be properly typed
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

    public function getSyndicationChannel(): string
    {
        return $this->syndicationChannel;
    }

    public function getCatalog(string $catalogCode): array
    {
        $filteredCatalogs = array_values(array_filter($this->catalogs, function ($catalogs) use ($catalogCode) {
            return $catalogCode === $catalogs['code'];
        }));

        if (0 === count($filteredCatalogs)) {
            throw new \InvalidArgumentException(sprintf('Catalog "%s" does not exist', $catalogCode));
        }

        return $filteredCatalogs[0];
    }

    public function normalizeForExternalApi(): array
    {
        return [
            'code' => $this->code,
            'label' => $this->label,
            'catalogProjections' => $this->catalogs,
            'getSyndicationChannel' => $this->getSyndicationChannel(),
        ];
    }
}
