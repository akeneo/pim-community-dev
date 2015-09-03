<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;

class CategoryFieldSetterSpec extends ObjectBehavior
{
    function let(
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->beConstructedWith(
            $categoryRepository,
            ['categories']
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\SetterInterface');
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\FieldSetterInterface');
    }

    function it_supports_categories_field()
    {
        $this->supportsField('categories')->shouldReturn(true);
        $this->supportsField('groups')->shouldReturn(false);
    }

    function it_checks_valid_data_format(ProductInterface $product)
    {
        $this->shouldThrow(
            InvalidArgumentException::arrayExpected(
                'categories',
                'setter',
                'category',
                'string'
            )
        )->during('setFieldData', [$product, 'categories', 'not an array']);

        $this->shouldThrow(
            InvalidArgumentException::arrayStringValueExpected(
                'categories',
                0,
                'setter',
                'category',
                'array'
            )
        )->during('setFieldData', [$product, 'categories', [['array of array']]]);
    }

    function it_sets_category_field(
        $categoryRepository,
        ProductInterface $product,
        CategoryInterface $mug,
        CategoryInterface $shirt,
        CategoryInterface $men
    ) {
        $categoryRepository->findOneByIdentifier('mug')->willReturn($mug);
        $categoryRepository->findOneByIdentifier('shirt')->willReturn($shirt);

        $product->getCategories()->willReturn([$men]);

        $product->removeCategory($men)->shouldBeCalled();

        $product->addCategory($mug)->shouldBeCalled();
        $product->addCategory($shirt)->shouldBeCalled();

        $this->setFieldData($product, 'categories', ['mug', 'shirt']);
    }

    function it_fails_if_one_of_the_category_code_does_not_exist(
        $categoryRepository,
        ProductInterface $product,
        CategoryInterface $mug,
        CategoryInterface $shirt
    ) {
        $categoryRepository->findOneByIdentifier('mug')->willReturn($mug);
        $categoryRepository->findOneByIdentifier('non valid category code')->willReturn(null);

        $this->shouldThrow(
            InvalidArgumentException::expected(
                'categories',
                'existing category code',
                'setter',
                'category',
                'non valid category code'
            )
        )->during('setFieldData', [$product, 'categories', ['mug', 'non valid category code']]);
    }
}
