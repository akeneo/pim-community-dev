<?php

namespace Akeneo\Platform\Syndication\Domain\Model;

class PlatformConfiguration
{
    public function __construct(
        private string $code,
        private string $label,
        private array $configuration // Should be improved and be properly typed
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

    public function getCatalog(string $catalogCode): array
    {
        $filteredCatalogs = array_values(array_filter($this->configuration, function ($configuration) use ($catalogCode) {
            return $catalogCode === $configuration['code'];
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
            'catalogProjections' => $this->configuration,
        ];
    }
}
