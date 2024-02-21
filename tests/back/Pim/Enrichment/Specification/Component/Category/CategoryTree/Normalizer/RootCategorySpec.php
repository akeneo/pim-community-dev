<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Normalizer;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Normalizer\RootCategory as NormalizerRootCategory;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\RootCategory as ReadModelRootCategory;
use PhpSpec\ObjectBehavior;


class RootCategorySpec extends ObjectBehavior
{
    function it_is_a_root_category_normalizer()
    {
        $this->shouldHaveType(NormalizerRootCategory::class);
    }

    function it_normalize_a_list_of_root_categories()
    {
        $categories = [
            new ReadModelRootCategory(1, 'tree_1', 'Tree 1', 2, true),
            new ReadModelRootCategory(2, 'tree_2', 'Tree 2', 1, false),
        ];

        $this->normalizeList($categories)->shouldReturn([
            [
                'id' => 1,
                'code' => 'tree_1',
                'label' => 'Tree 1 (2)',
                'selected' => "true"
            ],
            [
                'id' => 2,
                'code' => 'tree_2',
                'label' => 'Tree 2 (1)',
                'selected' => "false"
            ],
        ]);
    }
}
