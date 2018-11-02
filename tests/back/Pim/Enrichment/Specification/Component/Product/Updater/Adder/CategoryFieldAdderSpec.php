<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\CategoryFieldAdder;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\FieldAdderInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

class CategoryFieldAdderSpec extends ObjectBehavior
{
    function let(
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->beConstructedWith(
            $categoryRepository,
            ['categories']
        );
    }

    function it_is_a_adder()
    {
        $this->shouldImplement(AdderInterface::class);
        $this->shouldImplement(FieldAdderInterface::class);
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
                CategoryFieldAdder::class,
                'not an array'
            )
        )->during('addFieldData', [$product, 'categories', 'not an array']);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'categories',
                'one of the category codes is not a string, "array" given',
                CategoryFieldAdder::class,
                [['array of array']]
            )
        )->during('addFieldData', [$product, 'categories', [['array of array']]]);
    }

    function it_adds_category_field(
        $categoryRepository,
        ProductInterface $product,
        CategoryInterface $mug,
        CategoryInterface $shirt,
        CategoryInterface $men
    ) {
        $categoryRepository->findOneByIdentifier('mug')->willReturn($mug);
        $categoryRepository->findOneByIdentifier('shirt')->willReturn($shirt);

        $product->getCategories()->willReturn([$men]);

        $product->addCategory($mug)->shouldBeCalled();
        $product->addCategory($shirt)->shouldBeCalled();

        $this->addFieldData($product, 'categories', ['mug', 'shirt']);
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
            InvalidPropertyException::validEntityCodeExpected(
                'categories',
                'category code',
                'The category does not exist',
                CategoryFieldAdder::class,
                'non valid category code'
            )
        )->during('addFieldData', [$product, 'categories', ['mug', 'non valid category code']]);
    }
}
