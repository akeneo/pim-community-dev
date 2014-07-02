<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

use Prophecy\Argument;

use Pim\Bundle\CatalogBundle\CatalogEvents;

use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;

class CategoryManagerSpec extends ObjectBehavior
{
    const CATEGORY_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Category';

    function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        CategoryRepository $categoryRepository
    ) {
        $this->beConstructedWith($objectManager, self::CATEGORY_CLASS, $eventDispatcher);

        $objectManager->getRepository(self::CATEGORY_CLASS)->willReturn($categoryRepository);
    }

    function it_should_return_object_manager($objectManager)
    {
        $this->getObjectManager()->shouldReturn($objectManager);
    }

    function it_should_return_category_instance()
    {
        $this->getCategoryInstance()->shouldReturnAnInstanceOf(self::CATEGORY_CLASS);
    }

    function it_should_return_a_tree_instance(CategoryInterface $tree)
    {
        $this->getCategoryInstance()->shouldReturnAnInstanceOf(self::CATEGORY_CLASS);
    }

    function it_should_return_category_class_name()
    {
        $this->getCategoryClass()->shouldReturn(self::CATEGORY_CLASS);
    }

    function it_should_return_category_repository($objectManager, $categoryRepository)
    {
        $objectManager->getRepository(self::CATEGORY_CLASS)->willReturn($categoryRepository);
        $this->getEntityRepository()->shouldReturn($categoryRepository);
    }

    function it_should_return_category_by_code($categoryRepository, CategoryInterface $category)
    {
        $categoryRepository->findOneBy(['code' => 'bar'])->willReturn($category);

        $this->getCategoryByCode('bar');
    }

    function it_should_return_tree_by_code($categoryRepository, CategoryInterface $tree)
    {
        $categoryRepository->findOneBy(['code' => 'foo', 'parent' => null])->willReturn($tree);

        $this->getTreeByCode('foo')->shouldReturn($tree);
    }

    function it_should_dispatch_an_event_when_remove_a_category(
        $eventDispatcher,
        $objectManager,
        CategoryInterface $category,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $category->getProducts()->willReturn([$product1, $product2]);
        $product1->removeCategory($category)->shouldBeCalled();
        $product2->removeCategory($category)->shouldBeCalled();

        $eventDispatcher->dispatch(
            CatalogEvents::PRE_REMOVE_CATEGORY,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $objectManager->remove($category)->shouldBeCalled();

        $this->remove($category);
    }
}
