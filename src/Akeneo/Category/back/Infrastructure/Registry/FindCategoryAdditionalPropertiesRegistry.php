<?php

namespace Akeneo\Category\Infrastructure\Registry;

use Akeneo\Category\Application\Registry\FindCategoryAdditionalProperties;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\ServiceApi\Handler\CategoryAdditionalPropertiesFinder;

class FindCategoryAdditionalPropertiesRegistry implements FindCategoryAdditionalProperties
{
    /**
     * @param iterable<string, CategoryAdditionalPropertiesFinder> $additionalPropertiesFinder
     */
    public function __construct(
        private readonly iterable $additionalPropertiesFinder,
    ) {
    }

    public function forCategory(string $originalHttpRequestType, Category $category): Category
    {
        foreach ($this->additionalPropertiesFinder as $finder) {
            if ($finder->isSupportedAdditionalProperties() === true && $finder->originalHttpRequestType() === $originalHttpRequestType) {
                return $finder->execute($category);
            }
        }

        return $category;
    }
}
