<?php

namespace Specification\Akeneo\Category\Application\Handler;

use Akeneo\Category\Application\Handler\FindCategoryAdditionalPropertiesRegistry;
use Akeneo\Category\Domain\Model\Enrichment\Category;
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

    function it_executes_finder_if_it_is_supported(
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
}
