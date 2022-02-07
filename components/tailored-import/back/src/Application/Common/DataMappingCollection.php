<?php

namespace Akeneo\Platform\TailoredImport\Application\Common;

use Webmozart\Assert\Assert;

class DataMappingCollection
{
    private function __construct(
        private array $dataMappings
    )
    {
        Assert::allIsInstanceOf($this->dataMappings, DataMapping::class);
    }

    public static function createFromNormalized(array $normalizedDataMappings): self
    {
        $dataMappingInstances = array_map(static fn(array $dataMappingNormalized) => DataMapping::createFromNormalized($dataMappingNormalized), $normalizedDataMappings);
        return new self($dataMappingInstances);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->dataMappings);
    }
}