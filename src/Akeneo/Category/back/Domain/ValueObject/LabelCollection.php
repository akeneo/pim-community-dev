<?php

namespace Akeneo\Category\Domain\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @implements \IteratorAggregate<string, string>
 *
 * @phpstan-type Locale string
 * @phpstan-type LocalizedLabels array<Locale, string>
 */
final class LabelCollection implements \IteratorAggregate
{
    /**
     * @param LocalizedLabels $translatedLabels
     */
    private function __construct(private ?array $translatedLabels)
    {
        Assert::nullOrIsArray($translatedLabels);
        Assert::allStringNotEmpty(array_keys($translatedLabels));
    }

    /**
     * @param LocalizedLabels $translatedLabels
     */
    public static function fromArray(array $translatedLabels): self
    {
        return new self($translatedLabels);
    }

    /**
     * @return LocalizedLabels
     */
    public function getTranslations(): array
    {
        return $this->translatedLabels;
    }

    public function getTranslation(string $localeCode): ?string
    {
        return $this->translatedLabels[$localeCode] ?? null;
    }

    public function setTranslation(string $localeCode, ?string $label): void
    {
        Assert::notEmpty($localeCode);
        $this->translatedLabels[$localeCode] = $label;
    }

    public function hasTranslation(string $localeCode): bool
    {
        return array_key_exists($localeCode, $this->translatedLabels);
    }

    /**
     * @return \ArrayIterator<string, string>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->translatedLabels);
    }

    /**
     * @return LocalizedLabels
     */
    public function normalize(): array
    {
        return $this->translatedLabels ?? [];
    }
}
