<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Domain\Model;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LabelCollection
{
    /** @var array */
    private $translatedLabels;

    private function __construct(array $translatedLabels)
    {
        $this->translatedLabels = $translatedLabels;
    }

    public static function fromArray(array $translatedLabels): self
    {
        foreach ($translatedLabels as $code => $label) {
            if (!is_string($code)) {
                throw new \InvalidArgumentException(sprintf('Expecting locale code to be a string, %s given.', $code));
            }

            if (!is_string($label)) {
                throw new \InvalidArgumentException(sprintf('Expecting label to be a string, %s given.', $label));
            }
        }

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

    public function getLocaleCodes(): array
    {
        return array_keys($this->translatedLabels);
    }
}
