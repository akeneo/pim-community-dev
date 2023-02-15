<?php

namespace Akeneo\Category\Application\Storage;

interface UpdateCategoryEnrichedValues
{
    /**
     * @param array<string, string> $enrichedValuesByCode
     */
    public function execute(array $enrichedValuesByCode): void;
}
