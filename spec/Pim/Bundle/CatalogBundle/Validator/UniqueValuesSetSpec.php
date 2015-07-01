<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class UniqueValuesSetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\UniqueValuesSet');
    }

    function it_adds_value_if_not_present(
        ProductValueInterface $notPresent,
        ProductInterface $product,
        AttributeInterface $attribute
    ) {
        $notPresent->getProduct()->willReturn($product);
        $notPresent->getData()->willReturn('new-data');
        $notPresent->getAttribute()->willReturn($attribute);
        $notPresent->getLocale()->willReturn(null);
        $notPresent->getScope()->willReturn(null);
        $attribute->getCode()->willReturn('sku');

        $this->addValue($notPresent)->shouldReturn(true);
    }

    function it_does_not_add_value_if_already_present(
        ProductValueInterface $notPresent,
        ProductInterface $product,
        AttributeInterface $attribute,
        ProductValueInterface $present,
        ProductInterface $anotherProduct
    ) {
        $notPresent->getProduct()->willReturn($product);
        $notPresent->getData()->willReturn('new-data');
        $notPresent->getAttribute()->willReturn($attribute);
        $notPresent->getLocale()->willReturn(null);
        $notPresent->getScope()->willReturn(null);
        $attribute->getCode()->willReturn('sku');
        $this->addValue($notPresent)->shouldReturn(true);

        $present->getProduct()->willReturn($anotherProduct);
        $present->getData()->willReturn('new-data');
        $present->getAttribute()->willReturn($attribute);
        $present->getLocale()->willReturn(null);
        $present->getScope()->willReturn(null);
        $attribute->getCode()->willReturn('sku');
        $this->addValue($present)->shouldReturn(false);
    }
}
