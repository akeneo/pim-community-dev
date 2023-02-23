<?php

namespace Akeneo\Category\Application\Enrichment\Filter;

interface CategoryDataFilter
{
    /**
     * @param array<string, array> $enrichedValues
     * @param array<string, mixed> $filteringKeys
     * @return array<string, string>
     */
    public function filterCategoryToClean(array $enrichedValues, array $filteringKeys): array;
}
