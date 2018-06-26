<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\back\Domain\Model;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
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

            if ('' === $code) {
                throw new \InvalidArgumentException('Locale code cannot be empty.');
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
