<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\EnabledFieldSetter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EnabledFieldSetterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['enabled']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EnabledFieldSetter::class);
    }

    function it_is_a_field_setter()
    {
        $this->shouldImplement(FieldSetterInterface::class);
    }

    function it_checks_if_field_is_supported()
    {
        $this->supportsField('enabled')->shouldReturn(true);
        $this->supportsField('not_supported_field')->shouldReturn(false);
    }

    function it_sets_fields_if_data_is_boolean(ProductInterface $product)
    {
        $product->setEnabled(Argument::any())->shouldBeCalledTimes(6);

        $this->setFieldData($product, 'enabled', true);
        $this->setFieldData($product, 'enabled', 1);
        $this->setFieldData($product, 'enabled', '1');

        $this->setFieldData($product, 'enabled', false);
        $this->setFieldData($product, 'enabled', 0);
        $this->setFieldData($product, 'enabled', '0');
    }

    function it_throws_an_exception_if_data_is_not_boolean(ProductInterface $product)
    {
        $product->setEnabled(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(
            InvalidPropertyTypeException::booleanExpected(
                'enabled',
                EnabledFieldSetter::class,
                'foo'
            )
        )->during('setFieldData', [$product, 'enabled', 'foo']);
    }

    function it_throws_an_exception_if_the_subject_is_not_a_product()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                ProductModel::class,
                ProductInterface::class
            )
        )->during('setFieldData', [new ProductModel(), 'enabled', true]);
    }
}
