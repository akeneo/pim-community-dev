<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Command\Category;

use Akeneo\Pim\Enrichment\Bundle\Command\Category\Category;
use PhpSpec\ObjectBehavior;

class CategorySpec extends ObjectBehavior
{
    public function let()
    {
        $categoryParent = new Category([
            'id' => 1,
            'parent_id' => null,
            'root' => 1,
            'code' => 'master',
            'lvl' => '0',
            'lft' => '1',
            'rgt' => '6',
        ]);

        $categoryChild1 = new Category([
            'id' => 2,
            'parent_id' => 1,
            'root' => 1,
            'code' => 'child1',
            'lvl' => '1',
            'lft' => '2',
            'rgt' => '3',
        ]);

        $categoryChild2 = new Category([
            'id' => 3,
            'parent_id' => 1,
            'root' => 1,
            'code' => 'child2',
            'lvl' => '1',
            'lft' => '4',
            'rgt' => '5',
        ]);

        $this->beConstructedWith($categoryParent, $categoryChild1, $categoryChild2);
    }

    public function it_links($categoryParent, $categoryChild1, $categoryChild2) {
        dump($categoryParent);
    }
}
