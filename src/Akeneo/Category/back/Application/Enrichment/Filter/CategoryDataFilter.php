<?php

namespace Akeneo\Category\Application\Enrichment;

interface CategoryDataFilter
{
    /**
     * @param array<string, string> $enrichedValues
     * @param array<string, mixed> $filteringKeys
     * @return array<string, string>
     */
    public function filterCategoryToClean(array $enrichedValues, array $filteringKeys): array;
}
