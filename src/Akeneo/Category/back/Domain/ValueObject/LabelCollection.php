<?php

namespace Akeneo\Category\Domain\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LabelCollection
{
    /**
     * @param array<string, string> $translatedLabels
     */
    private function __construct(private ?array $translatedLabels)
    {
        Assert::allString($translatedLabels);
        Assert::allStringNotEmpty(\array_keys($translatedLabels));
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

    public function setLabel(string $localeCode, string $label): void
    {
        $this->translatedLabels[$localeCode] = $label;
    }

    public function hasLabel(string $localeCode): bool
    {
        return array_key_exists($localeCode, $this->translatedLabels);
    }

    /**
     * @return array<string,string>
     */
    public function normalize(): array
    {
        return $this->translatedLabels ?? [];
    }
}
