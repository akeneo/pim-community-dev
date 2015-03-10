<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\AttributeSetterInterface;

class SetterRegistrySpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_gets_setter(
        AttributeInterface $color,
        AttributeInterface $description,
        AttributeInterface $price,
        AttributeSetterInterface $optionSetter,
        AttributeSetterInterface $textSetter
    ) {
        $color->getCode()->willReturn('color');
        $description->getCode()->willReturn('description');
        $price->getCode()->willReturn('price');

        $optionSetter->supports($color)->willReturn(true);
        $optionSetter->supports($description)->willReturn(false);

        $optionSetter->supports($price)->willReturn(false);
        $textSetter->supports($description)->willReturn(true);
        $textSetter->supports($price)->willReturn(false);

        $this->register($optionSetter);
        $this->register($textSetter);

        $this->get($color)->shouldReturn($optionSetter);
        $this->get($description)->shouldReturn($textSetter);
        $this->shouldThrow(
            new \LogicException(
                'Attribute "price" is not supported by any setter'
            )
        )->during('get', [$price]);
    }
}
