<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Association;

/*
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class LabelCollection
{
    private array $translatedLabels;

    private function __construct(array $translatedLabels)
    {
        $this->translatedLabels = $translatedLabels;
    }

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
