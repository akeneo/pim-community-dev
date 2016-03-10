<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Component\Catalog\Model\CategoryInterface;

class CategoryManagerSpec extends ObjectBehavior
{
    const CATEGORY_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Category';

    function let(
        ObjectManager $objectManager,
        CategoryRepositoryInterface $categoryRepository,
        SimpleFactoryInterface $categoryFactory,
        Category $category
    ) {
        $this->beConstructedWith($objectManager, $categoryRepository, $categoryFactory, self::CATEGORY_CLASS);
        $categoryFactory->create()->willReturn($category);

        $objectManager->getRepository(self::CATEGORY_CLASS)->willReturn($categoryRepository);
    }

    function it_provides_object_manager($objectManager)
    {
        $this->getObjectManager()->shouldReturn($objectManager);
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
}
