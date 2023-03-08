<?php

namespace Akeneo\Category\Application\Query;

use Akeneo\Category\Domain\ValueObject\ValueCollection;

interface GetEnrichedValuesPerCategoryCode
{
    /**
     * @return \Traversable<string, ValueCollection>
     */
    public function byBatchesOf(int $batchSize): \Traversable;
}
