<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductFieldUpdaterInterface;

class ProductUpdaterSpec extends ObjectBehavior
{
    function let(ProductFieldUpdaterInterface $productFieldUpdater)
    {
        $this->beConstructedWith($productFieldUpdater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Updater\ProductUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\UpdaterInterface');
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_product()
    {
        $this->shouldThrow(new \InvalidArgumentException('Expects a "Pim\Bundle\CatalogBundle\Model\ProductInterface", "stdClass" provided.'))->during(
            'update', [new \stdClass(), []]
        );
    }

    function it_sets_a_value($productFieldUpdater, ProductInterface $product1, ProductInterface $product2)
    {
        $products = [$product1, $product2];

        $productFieldUpdater
            ->setData($product1, 'field', 'data', ['locale' => 'fr_FR', 'scope' => 'ecommerce'])
            ->shouldBeCalled();
        $productFieldUpdater
            ->setData($product2, 'field', 'data', ['locale' => 'fr_FR', 'scope' => 'ecommerce'])
            ->shouldBeCalled();

        $this->setValue($products, 'field', 'data', 'fr_FR', 'ecommerce');
    }

    function it_copies_a_value(
        $productFieldUpdater,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $products = [$product1, $product2];
        $options = [
            'from_locale' => 'from_locale',
            'to_locale' => 'to_locale',
            'from_scope' => 'from_scope',
            'to_scope' => 'to_scope',
        ];

        $productFieldUpdater
            ->copyData($product1, $product1, 'from_field', 'to_field', $options)
            ->shouldBeCalled();
        $productFieldUpdater
            ->copyData($product2, $product2, 'from_field', 'to_field', $options)
            ->shouldBeCalled();

        $this->copyValue($products, 'from_field', 'to_field', 'from_locale', 'to_locale', 'from_scope', 'to_scope');
    }
}
