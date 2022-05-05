<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @implements \IteratorAggregate<int, DataMapping>
 */
class DataMappingCollection implements \IteratorAggregate
{
    private function __construct(
        private array $dataMappings,
    ) {
        Assert::allIsInstanceOf($this->dataMappings, DataMapping::class);
        Assert::notEmpty($this->dataMappings);
    }

    public static function create(array $dataMappings): self
    {
        return new self($dataMappings);
    }

    /**
     * @return DataMapping[]|\ArrayIterator<int, DataMapping>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->dataMappings);
    }
}
