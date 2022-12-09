<?php

namespace Akeneo\Category\ServiceApi\Handler;

use Akeneo\Category\Domain\Model\Enrichment\Category;

interface CategoryAdditionalPropertiesFinder
{
    public function execute(Category $category): Category;

    public function isSupportedAdditionalProperties(): bool;
}
