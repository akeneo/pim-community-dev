<?php

namespace Specification\Akeneo\Category\Infrastructure\Registry;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Infrastructure\Registry\FindCategoryAdditionalPropertiesRegistry;
use Akeneo\Category\ServiceApi\Handler\CategoryAdditionalPropertiesFinder;
use PhpSpec\ObjectBehavior;

class FindCategoryAdditionalPropertiesRegistrySpec extends ObjectBehavior
{
    function let(
        FindCategoryAdditionalPropertiesRegistry $unsupportedFinder,
        FindCategoryAdditionalPropertiesRegistry $supportedFinder,
    ) {
        $this->beConstructedWith(
            [
                $supportedFinder,
                $unsupportedFinder,
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FindCategoryAdditionalPropertiesRegistry::class);
    }

    function it_executes_only_supported_finder_for_category(
        CategoryAdditionalPropertiesFinder $supportedFinder,
        CategoryAdditionalPropertiesFinder $unsupportedFinder,
        Category $category
    ) {
        $unsupportedFinder->isSupportedAdditionalProperties()->willReturn(false);
        $unsupportedFinder->execute($category)->shouldNotBeCalled();
        $supportedFinder->isSupportedAdditionalProperties()->willReturn(true);
        $supportedFinder->execute($category)->shouldBeCalled();

        $this->forCategory($category);
    }

    function it_executes_only_supported_finder_for_categories(
        CategoryAdditionalPropertiesFinder $supportedFinder,
        CategoryAdditionalPropertiesFinder $unsupportedFinder,
        Category $category
    ) {
        $unsupportedFinder->isSupportedAdditionalProperties()->willReturn(false);
        $unsupportedFinder->execute($category)->shouldNotBeCalled();

        $supportedFinder->isSupportedAdditionalProperties()->willReturn(true);
        $supportedFinder->execute($category)->shouldBeCalled();

        $this->forCategories([$category]);
    }
}
