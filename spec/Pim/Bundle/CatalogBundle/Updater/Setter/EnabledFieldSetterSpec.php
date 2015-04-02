<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
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
        $this->shouldHaveType('\Pim\Bundle\CatalogBundle\Updater\Setter\EnabledFieldSetter');
    }

    function it_is_a_field_setter()
    {
        $this->shouldImplement('\Pim\Bundle\CatalogBundle\Updater\Setter\FieldSetterInterface');
    }

    function it_checks_if_field_is_supported()
    {
        $this->supportsField('enabled')->shouldReturn(true);
        $this->supportsField('not_supported_field')->shouldReturn(false);
    }

    function it_sets_fields_if_data_is_boolean(ProductInterface $product)
    {
        $product->setEnabled(true)->shouldBeCalled(3);
        $product->setEnabled(false)->shouldBeCalled(3);

        $this->setFieldData($product, 'enabled', true);
        $this->setFieldData($product, 'enabled', 1);
        $this->setFieldData($product, 'enabled', '1');

        $this->setFieldData($product, 'enabled', false);
        $this->setFieldData($product, 'enabled', 0);
        $this->setFieldData($product, 'enabled', '0');
    }

    function it_does_not_set_field_if_data_is_not_boolean(ProductInterface $product)
    {
        $product->setEnabled(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(
            new InvalidArgumentException(
                'Attribute or field "enabled" expects a boolean as data, "string" given (for setter enabled).'
            )
        )->duringSetFieldData($product, 'enabled', 'no');
    }
}
