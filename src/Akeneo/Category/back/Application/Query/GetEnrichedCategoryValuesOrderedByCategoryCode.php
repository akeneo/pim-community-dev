<?php

namespace Akeneo\Category\Application\Query;

interface GetEnrichedCategoryValuesOrderedByCategoryCode
{
    /**
     * @return array<string, string>
     */
    public function byLimitAndOffset(int $limit, int $offset): array;
}
