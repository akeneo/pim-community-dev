<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\ChildCategory;

class ChildCategorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(1, 'code', 'label', true, true, 10, [
            new ChildCategory(2, 'code', 'label', false, false, 0, [])
        ]);
    }

    function it_is_a_child_category()
    {
        $this->shouldHaveType(ChildCategory::class);
    }

    function it_has_an_id()
    {
        $this->id()->shouldReturn(1);
    }

    function it_has_a_code()
    {
        $this->code()->shouldReturn('code');
    }

    function it_has_a_label()
    {
        $this->label()->shouldReturn('label');
    }

    function it_is_selected_as_a_filter_in_the_product_grid()
    {
        $this->selectedAsFilter()->shouldReturn(true);
    }

    function it_is_a_leaf()
    {
        $this->isLeaf()->shouldReturn(true);
    }

    function it_has_the_number_of_product_in_the_category()
    {
        $this->numberProductsInCategory()->shouldReturn(10);
    }

    function it_has_children_categories()
    {
        $this->childrenCategoriesToExpand()->shouldBeLike([
            new ChildCategory(2, 'code', 'label', false, false, 0, [])
        ]);
    }
}
