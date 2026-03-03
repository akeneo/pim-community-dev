<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Normalizer;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Normalizer\ChildCategory as NormalizerChildCategory;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\ChildCategory as ReadModelChildCategory;
use PhpSpec\ObjectBehavior;

class ChildCategorySpec extends ObjectBehavior
{
    function it_is_a_child_category_normalizer()
    {
        $this->shouldHaveType(NormalizerChildCategory::class);
    }

    function it_normalize_a_list_of_children_categories()
    {
        $categories = [
            new ReadModelChildCategory(1, 'child_1', 'Child 1', false, false, 2, [
                new ReadModelChildCategory(2, 'child_2', 'Child 2', true, true, 1, []),
            ]),
            new ReadModelChildCategory(3, 'child_3', 'Child 3', false, false, 3, []),
        ];

        $this->normalizeList($categories)->shouldReturn([
            [
                'attr' => [
                    'id' => 'node_1',
                    'data-code' => 'child_1',
                ],
                'data' => 'Child 1 (2)',
                'state' => 'open',
                'children' => [[
                    'attr' => [
                        'id' => 'node_2',
                        'data-code' => 'child_2',
                    ],
                    'data' => 'Child 2 (1)',
                    'state' => 'leaf toselect',
                    'children' => [],
                ]],
            ],
            [
                'attr' => [
                    'id' => 'node_3',
                    'data-code' => 'child_3',
                ],
                'data' => 'Child 3 (3)',
                'state' => 'closed',
                'children' => [],
            ],
        ]);
    }
}
