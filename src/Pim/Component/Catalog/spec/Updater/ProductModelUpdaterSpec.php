<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Updater\ProductModelUpdater;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductModelUpdaterSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        ObjectUpdaterInterface $valuesUpdater
    ) {
        $this->beConstructedWith(
            $propertySetter,
            $valuesUpdater,
            ['categories'],
            ['identifier']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelUpdater::class);
    }

    function it_is_a_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_a_product_model($propertySetter, $valuesUpdater, ProductModelInterface $productModel)
    {
        $propertySetter->setData($productModel, 'categories', ['tshirt'])->shouldBeCalled();

        $valuesUpdater->update($productModel, [
            'name' => [
                'locale' => 'fr_FR',
                'scope' => 'null',
                'data' => 'T-shirt',
            ],
            'description' => [
                'locale' => 'fr_FR',
                'scope' => 'null',
                'data' => 'T-shirt super beau',
            ]
        ], [])->shouldBeCalled();

        $this->update($productModel, [
            'identifier' => 'product_model_identifier',
            'values' => [
                'name' => [
                    'locale' => 'fr_FR',
                    'scope' => 'null',
                    'data' => 'T-shirt',
                ],
                'description' => [
                    'locale' => 'fr_FR',
                    'scope' => 'null',
                    'data' => 'T-shirt super beau',
                ]
            ],
            'categories' => ['tshirt'],
        ])->shouldReturn($this);
    }

    function it_only_works_with_product_model(ProductInterface $product)
    {
        $this->shouldThrow(InvalidObjectException::class)->during('update', [$product, [], []]);
    }
}
