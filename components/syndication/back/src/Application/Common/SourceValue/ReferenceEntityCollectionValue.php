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

namespace Akeneo\Platform\Syndication\Application\Common\SourceValue;

use Webmozart\Assert\Assert;

class ReferenceEntityCollectionValue implements SourceValueInterface
{
    /** @var string[] */
    private array $recordCodes;

    /** @var string[] */
    private array $mappedReplacementValues;

    public function __construct(array $recordCodes, array $mappedReplacementValues = [])
    {
        Assert::allString($recordCodes);
        Assert::allString($mappedReplacementValues);

        $this->recordCodes = $recordCodes;
        $this->mappedReplacementValues = $mappedReplacementValues;
    }

    public function hasMappedValue(string $optionCode): bool
    {
        return array_key_exists($optionCode, $this->mappedReplacementValues);
    }

    public function getMappedValue(string $recordCode): string
    {
        if (!$this->hasMappedValue($recordCode)) {
            throw new \InvalidArgumentException('This record code is not mapped');
        }

        return $this->mappedReplacementValues[$recordCode];
    }

    public function getRecordCodes(): array
    {
        return $this->recordCodes;
    }
}
