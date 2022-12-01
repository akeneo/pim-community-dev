<?php

namespace Akeneo\Category\Application\Handler;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\ServiceApi\Handler\CategoryAdditionalPropertiesFinder;

class FindCategoryAdditionalPropertiesRegistry
{
    /**
     * @param iterable<string, CategoryAdditionalPropertiesFinder> $additionalPropertiesFinder
     */
    public function __construct(
        private iterable $additionalPropertiesFinder,
    ) {
    }

    public function forCategory(Category $category): Category
    {
        foreach ($this->additionalPropertiesFinder as $finder) {
            if ($finder->isSupportedAdditionalProperties() === true) {
                $category = $finder->execute($category);
            }
        }

        return $category;
    }
}
