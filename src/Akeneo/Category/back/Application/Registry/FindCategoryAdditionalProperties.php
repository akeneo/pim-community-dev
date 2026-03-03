<?php

namespace Akeneo\Category\Application\Registry;

use Akeneo\Category\Domain\Model\Enrichment\Category;

interface FindCategoryAdditionalProperties
{
    public function forCategory(Category $category): Category;

    /**
     * @param Category[] $categories
     *
     * @return Category[]
     */
    public function forCategories(array $categories): array;
}
