<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\Common\SourceValue;

use Webmozart\Assert\Assert;

class MultiSelectValue implements SourceValueInterface
{
    /**
     * @param string[] $optionCodes
     * @param array<string, string> $mappedReplacementValues
     */
    public function __construct(
        private array $optionCodes,
        private array $mappedReplacementValues = []
    ) {
        Assert::allString($optionCodes);
    }

    public function getOptionCodes(): array
    {
        return $this->optionCodes;
    }

    public function getMappedReplacementValues(): array
    {
        return $this->mappedReplacementValues;
    }

    public function hasMappedValue(string $optionCode): bool
    {
        return array_key_exists($optionCode, $this->mappedReplacementValues);
    }

    public function getMappedValue(string $optionCode): string
    {
        if (!$this->hasMappedValue($optionCode)) {
            throw new \InvalidArgumentException('This option code is not mapped');
        }

        return $this->mappedReplacementValues[$optionCode];
    }
}
