<?php

namespace Akeneo\Category\Api\Model\Template;

use Akeneo\Category\Domain\ValueObject\LabelCollection as LabelCollectionFromDomain;
use Webmozart\Assert\Assert;

/**
 * This model represents labels of a template category as exposed to the outside of the category bounded context
 * It resembles the eponymous internal domain model but can drift in the future.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @phpstan-type Locale string
 * @phpstan-type LocalizedLabels array<Locale, string>
 */
final class LabelCollection
{

    /**
     * @param LocalizedLabels $translatedLabels
     */
    private function __construct(private ?array $translatedLabels)
    {
        Assert::allString($translatedLabels);
        Assert::allStringNotEmpty(\array_keys($translatedLabels));
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
    public function getLabels(): array
    {
        return $this->translatedLabels;
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
     * @return LocalizedLabels
     */
    public function normalize(): array
    {
        return $this->translatedLabels;
    }
}
