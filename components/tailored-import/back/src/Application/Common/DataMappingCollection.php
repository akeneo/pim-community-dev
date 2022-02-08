<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\Common;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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