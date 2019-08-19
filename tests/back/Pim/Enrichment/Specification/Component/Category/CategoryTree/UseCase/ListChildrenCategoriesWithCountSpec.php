<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListChildrenCategoriesWithCount;

class ListChildrenCategoriesWithCountSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(1, 3, true, 1, 'en_US');
    }

    function it_is_a_query()
    {
        $this->shouldHaveType(ListChildrenCategoriesWithCount::class);
    }

    function it_has_the_child_category_id_to_expand()
    {
        $this->childrenCategoryIdToExpand()->shouldReturn(1);
    }

    function it_has_the_category_id_of_the_category_selected_as_filter_in_the_product_datagrid()
    {
        $this->categoryIdSelectedAsFilter()->shouldReturn(3);
    }

    function it_counts_including_sub_categories()
    {
        $this->countIncludingSubCategories()->shouldReturn(true);
    }

    function it_has_the_user_id_used_to_apply_permission()
    {
        $this->userId()->shouldReturn(1);
    }

    function it_has_the_locale_code_to_translate_the_label_of_the_categories($locale)
    {
        $this->translationLocaleCode()->shouldReturn('en_US');
    }
}
