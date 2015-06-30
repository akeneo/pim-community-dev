<?php

namespace spec\Pim\Component\Catalog\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\CategoryTranslation;
use Pim\Bundle\CatalogBundle\Entity\GroupTranslation;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Repository\CategoryRepositoryInterface;
use Prophecy\Argument;

class CategoryUpdaterSpec extends ObjectBehavior
{
    function let(CategoryRepositoryInterface $categoryRepository)
    {
        $this->beConstructedWith($categoryRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\CategoryUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_category()
    {
        $this->shouldThrow(
            new \InvalidArgumentException(
                'Expects a "Pim\Bundle\CatalogBundle\Model\CategoryInterface", "stdClass" provided.'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_category(
        $categoryRepository,
        CategoryInterface $category,
        CategoryInterface $categoryMaster,
        CategoryTranslation $translatable
    ) {
        $categoryRepository->findOneByIdentifier('master')->willReturn($categoryMaster);

        $category->getTranslation()->willReturn($translatable);
        $translatable->setLabel('Ma superbe catégorie')->shouldBeCalled();
        $category->setCode('mycode')->shouldBeCalled();
        $category->setParent($categoryMaster)->shouldBeCalled();
        $category->setLocale('fr_FR')->shouldBeCalled();
        $category->getId()->willReturn(null);

        $values = [
            'code'         => 'mycode',
            'parent'       => 'master',
            'labels'       => [
                'fr_FR' => 'Ma superbe catégorie',
            ],
        ];

        $this->update($category, $values, []);
    }
}
