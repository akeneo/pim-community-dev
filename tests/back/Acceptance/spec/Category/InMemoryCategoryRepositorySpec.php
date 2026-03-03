<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\Category;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Test\Acceptance\Category\InMemoryCategoryRepository;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;

class InMemoryCategoryRepositorySpec extends ObjectBehavior
{
    function let()
    {
        $rootCategory = new Category();
        $rootCategory->setCode('root');
        $this->save($rootCategory);

        $child1 = new Category();
        $child1->setCode('child1');
        $child1->setParent($rootCategory);
        $this->save($child1);
        $child11 = new Category();
        $child11->setCode('child11');
        $child11->setParent($child1);
        $this->save($child11);
        $child12 = new Category();
        $child12->setCode('child12');
        $child12->setParent($child1);
        $this->save($child12);

        $child2 = new Category();
        $child2->setCode('child2');
        $child2->setParent($rootCategory);
        $this->save($child2);

        $child3 = new Category();
        $child3->setCode('child3');
        $child3->setParent($rootCategory);
        $this->save($child3);
    }

    function it_is_a_channel_repository()
    {
        $this->shouldImplement(CategoryRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_is_initiliazable()
    {
        $this->shouldBeAnInstanceOf(InMemoryCategoryRepository::class);
    }

    function it_returns_the_category_children_codes()
    {
        $category = $this->findOneByIdentifier('root');
        $this->getAllChildrenCodes($category)->shouldReturn(['child1', 'child11', 'child12', 'child2', 'child3']);

        $category = $this->findOneByIdentifier('root');
        $this->getAllChildrenCodes($category, true)
            ->shouldReturn(['root', 'child1', 'child11', 'child12', 'child2', 'child3']);
    }

    function it_returns_all_categories()
    {
        $list = $this->findAll();
        $list->shouldBeArray();
        $list->shouldHaveCount(6);
        $list['root']->shouldBeAnInstanceOf(Category::class);
        $list['child1']->shouldBeAnInstanceOf(Category::class);
        $list['child11']->shouldBeAnInstanceOf(Category::class);
        $list['child12']->shouldBeAnInstanceOf(Category::class);
        $list['child2']->shouldBeAnInstanceOf(Category::class);
        $list['child3']->shouldBeAnInstanceOf(Category::class);
    }
}
