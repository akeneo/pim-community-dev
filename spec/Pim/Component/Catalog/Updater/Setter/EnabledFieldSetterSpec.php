<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Prophecy\Argument;

class EnabledFieldSetterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['enabled']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Component\Catalog\Updater\Setter\EnabledFieldSetter');
    }

    function it_is_a_field_setter()
    {
        $this->shouldImplement('\Pim\Component\Catalog\Updater\Setter\FieldSetterInterface');
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

    function it_sets_fields_if_data_is_not_boolean(ProductInterface $product)
    {
        $product->setEnabled(Argument::any())->shouldBeCalled();

        $this->setFieldData($product, 'enabled', 'foo');
    }
}
