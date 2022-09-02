<?php

namespace Akeneo\Channel\API\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LabelCollection
{
    /**
     * @param array<string, string> $translatedLabels
     */
    private function __construct(
        private array $translatedLabels
    ) {
    }

    /**
     * @param array<string, string> $translatedLabels
     */
    public static function fromArray(array $translatedLabels): self
    {
        return new self($translatedLabels);
    }

    public function getLabel(string $localeCode): ?string
    {
        return $this->translatedLabels[$localeCode] ?? null;
    }

    public function hasLabel(string $localeCode): bool
    {
        return array_key_exists($localeCode, $this->translatedLabels);
    }
}
