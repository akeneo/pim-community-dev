<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Event\CategoryEvents;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    function it_is_a_saver()
    {
        $this->shouldImplement('Pim\Component\Resource\Model\SaverInterface');
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Pim\Component\Resource\Model\RemoverInterface');
    }

    function it_provides_object_manager($objectManager)
    {
        $this->getObjectManager()->shouldReturn($objectManager);
    }

    function it_instantiates_a_category()
    {
        $this->getCategoryInstance()->shouldReturnAnInstanceOf(self::CATEGORY_CLASS);
    }

    function it_instantiates_a_tree(CategoryInterface $tree)
    {
        $this->getCategoryInstance()->shouldReturnAnInstanceOf(self::CATEGORY_CLASS);
    }

    function it_provides_the_category_class_name()
    {
        $this->getCategoryClass()->shouldReturn(self::CATEGORY_CLASS);
    }

    function it_provides_the_category_repository($objectManager, $categoryRepository)
    {
        $objectManager->getRepository(self::CATEGORY_CLASS)->willReturn($categoryRepository);
        $this->getEntityRepository()->shouldReturn($categoryRepository);
    }

    function it_provides_a_category_from_his_code($categoryRepository, CategoryInterface $category)
    {
        $categoryRepository->findOneBy(['code' => 'bar'])->willReturn($category);

        $this->getCategoryByCode('bar');
    }

    function it_provides_a_tree_from_his_code($categoryRepository, CategoryInterface $tree)
    {
        $categoryRepository->findOneBy(['code' => 'foo', 'parent' => null])->willReturn($tree);

        $this->getTreeByCode('foo')->shouldReturn($tree);
    }

    function it_dispatches_an_event_when_removing_a_category(
        $eventDispatcher,
        $objectManager,
        CategoryInterface $category,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $category->isRoot()->willReturn(false);
        $category->getProducts()->willReturn([$product1, $product2]);
        $product1->removeCategory($category)->shouldBeCalled();
        $product2->removeCategory($category)->shouldBeCalled();

        $eventDispatcher->dispatch(
            CategoryEvents::PRE_REMOVE_CATEGORY,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $objectManager->remove($category)->shouldBeCalled();

        $this->remove($category, ['flush' => false]);
    }

    function it_dispatches_an_event_when_removing_a_tree(
        $eventDispatcher,
        $objectManager,
        CategoryInterface $tree
    ) {
        $tree->isRoot()->willReturn(true);
        $tree->getProducts()->willReturn([]);

        $eventDispatcher->dispatch(
            CategoryEvents::PRE_REMOVE_TREE,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $objectManager->remove($tree)->shouldBeCalled();

        $this->remove($tree, ['flush' => false]);
    }

    function it_throws_exception_when_save_anything_else_than_a_category()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a Pim\Bundle\CatalogBundle\Model\CategoryInterface, "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringSave($anythingElse);
    }

    function it_throws_exception_when_remove_anything_else_than_a_category()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a Pim\Bundle\CatalogBundle\Model\CategoryInterface, "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringRemove($anythingElse);
    }
}
