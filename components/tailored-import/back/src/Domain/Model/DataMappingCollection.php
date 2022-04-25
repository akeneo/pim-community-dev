<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
