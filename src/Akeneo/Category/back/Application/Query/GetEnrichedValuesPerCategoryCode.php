<?php

namespace Akeneo\Category\Application\Query;

interface GetEnrichedValuesPerCategoryCode
{
    public function byBatchesOf(int $batchSize): \Generator;
}
