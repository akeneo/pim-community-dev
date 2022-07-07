<?php
declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Cli\CheckCategoryTrees;

use Akeneo\Category\Infrastructure\Cli\CheckCategoryTrees\Category;
use PhpSpec\ObjectBehavior;

class CategorySpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith($this->getMasterCategory());
    }

    public function it_reorders_the_category_tree() {
        $this->addChild($this->getDisorderedCategories()['child1']);
        $this->addChild($this->getDisorderedCategories()['child2']);
        $this->addChild($this->getDisorderedCategories()['child3']);

        $expectedCategory = new Category($this->getMasterCategory());
        $expectedCategory->addChild($this->getCategories()['child1']);
        $expectedCategory->addChild($this->getCategories()['child2']);
        $expectedCategory->addChild($this->getCategories()['child3']);

        $this->reorder()->shouldBeLike($expectedCategory);
    }

    public function it_displays_diff_between_categories()
    {
        $this->addChild($this->getDisorderedCategories()['child1']);
        $this->addChild($this->getDisorderedCategories()['child2']);
        $this->addChild($this->getDisorderedCategories()['child3']);
        $this->addChild($this->getDisorderedCategories()['child4']);

        $expectedCategory = new Category($this->getMasterCategory());
        $expectedCategory->addChild($this->getCategories()['child1']);
        $expectedCategory->addChild($this->getCategories()['child2']);
        $expectedCategory->addChild($this->getCategories()['child3']);

        $this->diff($expectedCategory)->shouldReturn([
            0 => "id=1 code=master : Children count mismatch (has:4, expected:3)",
            1 => "Child at index 0: id=2 code=child1 : Left mismatch (has:3, expected:2)",
            2 => "Child at index 0: id=2 code=child1 : Right mismatch (has:2, expected:3)",
            3 => "Child at index 1: id=3 code=child2 : Right mismatch (has:7, expected:5)",
            4 => "Child at index 2: id=4 code=child3 : Level mismatch (has:2, expected:1)",
        ]);
    }

    private function getMasterCategory(): array
    {
        return [
            'id' => 1,
            'parent_id' => null,
            'root' => 1,
            'code' => 'master',
            'lvl' => '0',
            'lft' => '1',
            'rgt' => '8',
        ];
    }

    private function getCategories(): array
    {
        return [
            'child1' => new Category([
                'id' => 2,
                'parent_id' => 1,
                'root' => 1,
                'code' => 'child1',
                'lvl' => '1',
                'lft' => '2',
                'rgt' => '3',
            ]),
            'child2' => new Category([
                'id' => 3,
                'parent_id' => 1,
                'root' => 1,
                'code' => 'child2',
                'lvl' => '1',
                'lft' => '4',
                'rgt' => '5',
            ]),
            'child3' => new Category([
                'id' => 4,
                'parent_id' => 1,
                'root' => 1,
                'code' => 'child3',
                'lvl' => '1',
                'lft' => '6',
                'rgt' => '7',
            ]),
        ];
    }

    private function getDisorderedCategories(): array
    {
        return [
            'child1' => new Category([
                'id' => 2,
                'parent_id' => 1,
                'root' => 1,
                'code' => 'child1',
                'lvl' => '1',
                'lft' => '3',
                'rgt' => '2',
            ]),
            'child2' => new Category([
                'id' => 3,
                'parent_id' => 1,
                'root' => 1,
                'code' => 'child2',
                'lvl' => '1',
                'lft' => '4',
                'rgt' => '7',
            ]),
            'child3' => new Category([
                'id' => 4,
                'parent_id' => 1,
                'root' => 1,
                'code' => 'child3',
                'lvl' => '2',
                'lft' => '6',
                'rgt' => '7',
            ]),
            'child4' => new Category([
                'id' => 5,
                'parent_id' => 1,
                'root' => 1,
                'code' => 'child4',
                'lvl' => '1',
                'lft' => '6',
                'rgt' => '7',
            ]),
        ];
    }
}
