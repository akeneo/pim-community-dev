<?php

namespace Akeneo\Category\Application\Query;

use Akeneo\Category\Domain\ValueObject\ValueCollection;

interface GetEnrichedValuesPerCategoryCode
{
    /**
     * @return \Generator
     */
    public function byBatchesOf(int $batchSize): \Generator;
}
