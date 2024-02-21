<?php

namespace Akeneo\Category\Application\Storage;

use Akeneo\Category\Domain\ValueObject\ValueCollection;

interface UpdateCategoryEnrichedValues
{
    /**
     * @param array<string, ValueCollection> $enrichedValuesByCode
     */
    public function execute(array $enrichedValuesByCode): void;
}
