<?php

namespace Akeneo\Category\Application\Registry;

use Akeneo\Category\Domain\Model\Enrichment\Category;

interface FindCategoryAdditionalProperties
{
    public function forCategory(string $originalHttpRequestType, Category $category): Category;
}
