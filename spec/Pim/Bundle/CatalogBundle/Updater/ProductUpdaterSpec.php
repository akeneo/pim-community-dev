<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater;

use Akeneo\Component\StorageUtils\Updater\PropertyCopierInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Validator\ValidatorInterface;

class ProductUpdaterSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        PropertyCopierInterface $propertyCopier,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith($propertySetter, $propertyCopier, $validator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Updater\ProductUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_product()
    {
        $this->shouldThrow(new \InvalidArgumentException('Expects a "Pim\Bundle\CatalogBundle\Model\ProductInterface", "stdClass" provided.'))->during(
            'update', [new \stdClass(), []]
        );
    }

    function it_sets_a_value($propertySetter, ProductInterface $product1, ProductInterface $product2)
    {
        $products = [$product1, $product2];

        $propertySetter
            ->setData($product1, 'field', 'data', ['locale' => 'fr_FR', 'scope' => 'ecommerce'])
            ->shouldBeCalled();
        $propertySetter
            ->setData($product2, 'field', 'data', ['locale' => 'fr_FR', 'scope' => 'ecommerce'])
            ->shouldBeCalled();

        $this->setValue($products, 'field', 'data', 'fr_FR', 'ecommerce');
    }

    function it_copies_a_value(
        $propertyCopier,
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

        $propertyCopier
            ->copyData($product1, $product1, 'from_field', 'to_field', $options)
            ->shouldBeCalled();
        $propertyCopier
            ->copyData($product2, $product2, 'from_field', 'to_field', $options)
            ->shouldBeCalled();

        $this->copyValue($products, 'from_field', 'to_field', 'from_locale', 'to_locale', 'from_scope', 'to_scope');
    }
}
