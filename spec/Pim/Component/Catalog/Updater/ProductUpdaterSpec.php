<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Updater\PropertyCopierInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\Catalog\EmptyChecker\ProductValueStructuredData\EmptyCheckerInterface;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductUpdaterSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        PropertyCopierInterface $propertyCopier,
        ProductTemplateUpdaterInterface $templateUpdater,
        EmptyCheckerInterface $checker
    ) {
        $this->beConstructedWith($propertySetter, $propertyCopier, $templateUpdater, $checker);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\ProductUpdater');
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

    function it_updates_a_product($propertySetter, ProductInterface $product)
    {
        $propertySetter
            ->setData($product, 'groups', ['related1', 'related2'])
            ->shouldBeCalled();
        $propertySetter
            ->setData($product, 'name', 'newname', ['locale' => null, 'scope' => null])
            ->shouldBeCalled();
        $propertySetter
            ->setData($product, 'desc', 'newdescUS', ['locale' => 'en_US', 'scope' => null])
            ->shouldBeCalled();

        $updates = [
            'groups' => ['related1', 'related2'],
            'name' => [['data' => 'newname', 'locale' => null, 'scope' => null]],
            'desc' => [['data' => 'newdescUS', 'locale' => 'en_US', 'scope' => null]],
        ];

        $this->update($product, $updates, []);
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

    function it_applies_updates_for_non_empty_values($propertySetter, $checker, ProductInterface $product)
    {
        $toUpdate = [
            'not_empty_string' => [['data' => 'notemptydata', 'locale' => null, 'scope' => null]]
        ];

        $checker->isEmpty('not_empty_string', 'notemptydata')->willReturn(false);
        $propertySetter
            ->setData($product, 'not_empty_string', 'notemptydata', ['locale' => null, 'scope' => null])
            ->shouldBeCalled();

        $this->update($product, $toUpdate, []);
    }

    function it_does_not_apply_updates_for_empty_values($propertySetter, $checker, ProductInterface $product)
    {
        $toNotUpdate = [
            'empty_string' => [['data' => '', 'locale' => 'en_US', 'scope' => null]],
        ];

        $checker->isEmpty('empty_string', '')->willReturn(true);
        $propertySetter
            ->setData($product, 'empty_string', '', ['locale' => 'en_US', 'scope' => null])
            ->shouldNotBeCalled();

        $this->update($product, $toNotUpdate, []);
    }
}
