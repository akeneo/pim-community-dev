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
     * @phpstan-var LocalizedLabels
     */
    private array $labels = [];

    /**
     * @phpstan-param LocalizedLabels $labels
     */
    private function __construct(array $labels)
    {
        foreach ($labels as $localeCode => $label) {
            $this->setTranslation($localeCode, $label);
        }
    }

    /**
     * @phpstan-param LocalizedLabels $labels
     */
    public static function fromArray(array $labels): self
    {
        return new self($labels);
    }

    /**
     * @return LocalizedLabels
     */
    public function getTranslations(): array
    {
        return $this->labels;
    }

    public function getTranslation(string $localeCode): ?string
    {
        return $this->labels[$localeCode] ?? null;
    }

    public function setTranslation(string $localeCode, ?string $label): void
    {
        Assert::notEmpty($localeCode);
        Assert::nullOrMaxLength($label, 255);

        $this->labels[$localeCode] = empty($label) ? null : $label;
    }

    public function hasTranslation(string $localeCode): bool
    {
        return array_key_exists($localeCode, $this->labels);
    }

    public function merge(LabelCollection $labelCollection): void
    {
        foreach ($labelCollection as $localeCode => $label) {
            $this->setTranslation($localeCode, $label);
        }
    }

    /**
     * @return \ArrayIterator<string, string>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->labels);
    }

    /**
     * @return LocalizedLabels
     */
    public function normalize(): array
    {
        return $this->labels;
    }
}
