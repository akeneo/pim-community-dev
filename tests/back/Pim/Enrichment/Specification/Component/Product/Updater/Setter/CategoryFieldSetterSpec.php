<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownCategoryException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\CategoryFieldSetter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

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
        $this->shouldImplement(SetterInterface::class);
        $this->shouldImplement(FieldSetterInterface::class);
    }

    function it_supports_categories_field()
    {
        $this->supportsField('categories')->shouldReturn(true);
        $this->supportsField('groups')->shouldReturn(false);
    }

    function it_checks_valid_data_format(ProductInterface $product)
    {
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'categories',
                CategoryFieldSetter::class,
                'not an array'
            )
        )->during('setFieldData', [$product, 'categories', 'not an array']);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'categories',
                'one of the category codes is not a string, "array" given',
                CategoryFieldSetter::class,
                [['array of array']]
            )
        )->during('setFieldData', [$product, 'categories', [['array of array']]]);
    }

    function it_sets_category_field(
        $categoryRepository,
        ProductInterface $product
    ) {
        $mug = (new Category())->setCode('mug');
        $shirt = (new Category())->setCode('shirt');
        $categoryRepository->findOneByIdentifier('mug')->willReturn($mug);
        $categoryRepository->findOneByIdentifier('shirt')->willReturn($shirt);
        $shoes = (new Category())->setCode('shoes');

        $product->getCategories()->willReturn(new ArrayCollection([$mug, $shoes]));

        $product->removeCategory($shoes)->shouldBeCalled();
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

        $this
            ->shouldThrow(UnknownCategoryException::class)
            ->during('setFieldData', [$product, 'categories', ['mug', 'non valid category code']]);
    }
}
