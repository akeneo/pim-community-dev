<?php

namespace spec\Pim\Bundle\EnrichBundle\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager;
use Prophecy\Argument;

class CategoryExtensionSpec extends ObjectBehavior
{
    function let(ProductCategoryManager $manager)
    {
        $productsLimitForRemoval = 10;
        $this->beConstructedWith($manager, $productsLimitForRemoval);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('\Twig_Extension');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_category_extension');
    }

    function it_registers_category_functions()
    {
        $functions = $this->getFunctions();
        $functions->shouldHaveCount(6);
        $functions->shouldHaveKey('children_response');
        $functions->shouldHaveKey('children_tree_response');
        $functions->shouldHaveKey('list_categories_response');
        $functions->shouldHaveKey('list_trees_response');
        $functions->shouldHaveKey('exceeds_products_limit_for_removal');
        $functions->shouldHaveKey('get_products_limit_for_removal');
    }

    function it_formats_trees_with_products_count($manager, Category $tree1, Category $tree2)
    {
        $manager->getProductsCountInCategory(Argument::any(), false)->willReturn(5);

        $tree1->getId()->willReturn(1);
        $tree1->getLabel()->willReturn('Selected tree');
        $tree1->hasChildren()->willReturn(false);
        $tree1->isRoot()->willReturn(true);

        $tree2->getId()->willReturn(2);
        $tree2->getLabel()->willReturn('Master catalog');
        $tree2->hasChildren()->willReturn(false);
        $tree2->isRoot()->willReturn(true);

        $expected = [
            ['id' => 1, 'label' => 'Selected tree (5)', 'selected' => 'true'],
            ['id' => 2, 'label' => 'Master catalog (5)', 'selected' => 'false']
        ];

        $this->listTreesResponse([$tree1, $tree2], 1)->shouldEqualUsingJSON($expected);
    }

    function it_formats_trees_without_products_count($manager, Category $tree1, Category $tree2)
    {
        $manager->getProductsCountInCategory(Argument::any(), false)->willReturn(5);

        $tree1->getId()->willReturn(1);
        $tree1->getLabel()->willReturn('Selected tree');
        $tree1->hasChildren()->willReturn(false);
        $tree1->isRoot()->willReturn(true);

        $tree2->getId()->willReturn(2);
        $tree2->getLabel()->willReturn('Master catalog');
        $tree2->hasChildren()->willReturn(false);
        $tree2->isRoot()->willReturn(true);

        $expected = [
            ['id' => 1, 'label' => 'Selected tree', 'selected' => 'true'],
            ['id' => 2, 'label' => 'Master catalog', 'selected' => 'false']
        ];

        $this->listTreesResponse([$tree1, $tree2], 1, false)->shouldEqualUsingJSON($expected);
    }

    function it_formats_a_list_of_categories_with_product_count(
        $manager,
        Category $root,
        Category $category1,
        Category $category2
    ) {
        $manager->getProductsCountInCategory(Argument::any(), false)->willReturn(5);

        $root->getId()->willReturn(1);
        $root->getLabel()->willReturn('Root');
        $root->hasChildren()->willReturn(true);
        $root->isRoot()->willReturn(true);

        $category1->getId()->willReturn(2);
        $category1->getLabel()->willReturn('Selected category');
        $category1->hasChildren()->willReturn(false);
        $category1->isRoot()->willReturn(false);
        $category1Array = [
            'item'       => $category1,
            '__children' => []
        ];

        $category2->getId()->willReturn(3);
        $category2->getLabel()->willReturn('Some category');
        $category2->hasChildren()->willReturn(false);
        $category2->isRoot()->willReturn(false);
        $category2Array = [
            'item'       => $category2,
            '__children' => []
        ];

        $expected = [
            'attr'     => ['id' => 'node_1'],
            'data'     => 'Root (5)',
            'state'    => 'closed jstree-root',
            'children' => [
                [
                    'attr'     => ['id' => 'node_2'],
                    'data'     => 'Selected category (5)',
                    'state'    => 'leaf toselect jstree-checked',
                    'children' => []
                ],
                [
                    'attr'     => ['id' => 'node_3'],
                    'data'     => 'Some category (5)',
                    'state'    => 'leaf',
                    'children' => []
                ]
            ]
        ];

        $this->childrenTreeResponse([$category1Array, $category2Array], $category1, $root, true)->shouldEqualUsingJSON($expected);
    }

    function it_formats_a_list_of_categories_without_product_count(
        $manager,
        Category $root,
        Category $category1,
        Category $category2
    ) {
        $manager->getProductsCountInCategory(Argument::any(), false)->willReturn(5);

        $root->getId()->willReturn(1);
        $root->getLabel()->willReturn('Root');
        $root->hasChildren()->willReturn(true);
        $root->isRoot()->willReturn(true);

        $category1->getId()->willReturn(2);
        $category1->getLabel()->willReturn('Selected category');
        $category1->hasChildren()->willReturn(false);
        $category1->isRoot()->willReturn(false);
        $category1Array = [
            'item'       => $category1,
            '__children' => []
        ];

        $category2->getId()->willReturn(3);
        $category2->getLabel()->willReturn('Some category');
        $category2->hasChildren()->willReturn(false);
        $category2->isRoot()->willReturn(false);
        $category2Array = [
            'item'       => $category2,
            '__children' => []
        ];

        $expected = [
            'attr'     => ['id' => 'node_1'],
            'data'     => 'Root',
            'state'    => 'closed jstree-root',
            'children' => [
                [
                    'attr'     => ['id' => 'node_2'],
                    'data'     => 'Selected category',
                    'state'    => 'leaf toselect jstree-checked',
                    'children' => []
                ],
                [
                    'attr'     => ['id' => 'node_3'],
                    'data'     => 'Some category',
                    'state'    => 'leaf',
                    'children' => []
                ]
            ]
        ];

        $this->childrenTreeResponse([$category1Array, $category2Array], $category1, $root)->shouldEqualUsingJSON($expected);
    }

    function it_lists_categories_and_their_children_with_product_count(
        $manager,
        Category $category0,
        Category $category1,
        Category $category2
    ) {
        $manager->getProductsCountInCategory(Argument::any(), false)->willReturn(5);

        $category0->getId()->willReturn(1);
        $category0->getLabel()->willReturn('Selected category');
        $category0->hasChildren()->willReturn(true);
        $category0->isRoot()->willReturn(false);

        $category1->getId()->willReturn(2);
        $category1->getLabel()->willReturn('Sub-category 1');
        $category1->hasChildren()->willReturn(false);
        $category1->isRoot()->willReturn(false);

        $category2->getId()->willReturn(3);
        $category2->getLabel()->willReturn('Sub-category 2');
        $category2->hasChildren()->willReturn(false);
        $category2->isRoot()->willReturn(false);

        $expected = [
            'attr'     => ['id' => 'node_1'],
            'data'     => 'Selected category (5)',
            'state'    => 'closed',
            'children' => [
                [
                    'attr'  => ['id' => 'node_2'],
                    'data'  => 'Sub-category 1 (5)',
                    'state' => 'leaf'
                ],
                [
                    'attr'  => ['id' => 'node_3'],
                    'data'  => 'Sub-category 2 (5)',
                    'state' => 'leaf'
                ]
            ]
        ];

        $this->childrenResponse([$category1, $category2], $category0, true)->shouldEqualUsingJSON($expected);
    }

    function it_lists_categories_and_their_children_without_product_count(
        $manager,
        Category $category0,
        Category $category1,
        Category $category2
    ) {
        $manager->getProductsCountInCategory(Argument::any(), false)->willReturn(5);

        $category0->getId()->willReturn(1);
        $category0->getLabel()->willReturn('Selected category');
        $category0->hasChildren()->willReturn(true);
        $category0->isRoot()->willReturn(false);

        $category1->getId()->willReturn(2);
        $category1->getLabel()->willReturn('Sub-category 1');
        $category1->hasChildren()->willReturn(false);
        $category1->isRoot()->willReturn(false);

        $category2->getId()->willReturn(3);
        $category2->getLabel()->willReturn('Sub-category 2');
        $category2->hasChildren()->willReturn(false);
        $category2->isRoot()->willReturn(false);

        $expected = [
            'attr'     => ['id' => 'node_1'],
            'data'     => 'Selected category',
            'state'    => 'closed',
            'children' => [
                [
                    'attr'  => ['id' => 'node_2'],
                    'data'  => 'Sub-category 1',
                    'state' => 'leaf'
                ],
                [
                    'attr'  => ['id' => 'node_3'],
                    'data'  => 'Sub-category 2',
                    'state' => 'leaf'
                ]
            ]
        ];

        $this->childrenResponse([$category1, $category2], $category0)->shouldEqualUsingJSON($expected);
    }

    function it_lists_and_format_categories(
        $manager,
        Category $category0,
        Category $category1,
        Category $category2
    ) {
        $manager->getProductsCountInCategory(Argument::any(), false)->willReturn(5);

        $category1->getId()->willReturn(2);
        $category1->getLabel()->willReturn('Some category 1');
        $category1->hasChildren()->willReturn(false);
        $category1->isRoot()->willReturn(false);
        $category1Array = [
            'item'       => $category1,
            '__children' => []
        ];

        $category2->getId()->willReturn(3);
        $category2->getLabel()->willReturn('Some category 2');
        $category2->hasChildren()->willReturn(false);
        $category2->isRoot()->willReturn(false);
        $category2Array = [
            'item'       => $category2,
            '__children' => []
        ];

        $category0->getId()->willReturn(1);
        $category0->getLabel()->willReturn('Parent category');
        $category0->hasChildren()->willReturn(true);
        $category0->isRoot()->willReturn(false);
        $category0Array = [
            'item'       => $category0,
            '__children' => [$category1Array, $category2Array]
        ];

        $expected = [
            [
                'attr'     => ['id' => 'node_1'],
                'data'     => 'Parent category',
                'state'    => 'open',
                'children' => [
                    [
                        'attr'                  => ['id' => 'node_2'],
                        'data'                  => 'Some category 1',
                        'state'                 => 'leaf',
                        'children'              => [],
                        'selectedChildrenCount' => 0
                    ],
                    [
                        'attr'                  => ['id' => 'node_3'],
                        'data'                  => 'Some category 2',
                        'state'                 => 'leaf',
                        'children'              => [],
                        'selectedChildrenCount' => 0
                    ]
                ],
                'selectedChildrenCount' => 0
            ]
        ];

        $this->listCategoriesResponse([$category0Array], new ArrayCollection())->shouldEqualUsingJSON($expected);
    }

    function it_checks_if_a_category_exceeds_the_products_limit_for_removal($manager, Category $category)
    {
        $manager->getProductsCountInCategory(Argument::any(), true)->willReturn(11);
        $this->exceedsProductsLimitForRemoval($category, true)->shouldReturn(true);

        $manager->getProductsCountInCategory(Argument::any(), true)->willReturn(10);
        $this->exceedsProductsLimitForRemoval($category, true)->shouldReturn(false);
    }

    function it_gives_the_products_limit_for_removal()
    {
        $this->getProductsLimitForRemoval()->shouldReturn(10);
    }

    public function getMatchers()
    {
        return [
            'equalUsingJSON' => function ($subject, $value) {
                return json_encode($subject) === json_encode($value);
            }
        ];
    }
}
