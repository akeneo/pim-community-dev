<?php

namespace Akeneo\Category\Domain\ValueObject\Attribute;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @implements \IteratorAggregate<string, string>
 * @phpstan-type Locale string
 * @phpstan-type LocalizedAdditionalProperty
 * array<Locale, string>
 */
final class AttributeAdditionalProperties implements \IteratorAggregate
{
    /**
     * @param LocalizedAdditionalProperty
     * $translatedAdditionalProperty
     *
     */
    private function __construct(private ?array $translatedAdditionalProperty
    )
    {
        Assert::allString($translatedAdditionalProperty
        );
        Assert::allStringNotEmpty(\array_keys($translatedAdditionalProperty
        ));
    }

    /**
     * @param LocalizedAdditionalProperty
     * $translatedAdditionalProperty
     *
     */
    public static function fromArray(array $translatedAdditionalProperty
    ): self
    {
        return new self($translatedAdditionalProperty
        );
    }

    /**
     * @return LocalizedAdditionalProperty
     *
     */
    public function getTranslations(): array
    {
        return $this->translatedAdditionalProperty;
    }

    public function getTranslation(string $localeCode): ?string
    {
        return $this->translatedAdditionalProperty
        [$localeCode] ?? null;
    }

    public function setTranslation(string $localeCode, string $label): void
    {
        $this->translatedAdditionalProperty
        [$localeCode] = $label;
    }

    public function hasTranslation(string $localeCode): bool
    {
        return array_key_exists($localeCode, $this->translatedAdditionalProperty
        );
    }

    /**
     * @return \ArrayIterator<string, string>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->translatedAdditionalProperty
        );
    }

    /**
     * @return LocalizedAdditionalProperty
     *
     */
    public function normalize(): array
    {
        return $this->translatedAdditionalProperty
            ?? [];
    }
}
