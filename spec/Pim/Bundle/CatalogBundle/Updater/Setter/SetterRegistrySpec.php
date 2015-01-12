<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\SetterInterface;

class SetterRegistrySpec extends ObjectBehavior
{
    function it_gets_setter(
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeInterface $attribute3,
        SetterInterface $setter1,
        SetterInterface $setter2
    ) {
        $attribute1->getCode()->willReturn('attribute1Code');
        $attribute2->getCode()->willReturn('attribute2Code');
        $attribute3->getCode()->willReturn('attribute3Code');

        $setter1->supports($attribute1)->willReturn(true);
        $setter1->supports($attribute2)->willReturn(false);
        $setter1->supports($attribute3)->willReturn(false);
        $setter2->supports($attribute2)->willReturn(true);
        $setter2->supports($attribute3)->willReturn(false);

        $this->register($setter1);
        $this->register($setter2);

        $this->get($attribute1)->shouldReturn($setter1);
        $this->get($attribute2)->shouldReturn($setter2);
        $this->shouldThrow(
            new \LogicException(
                'Attribute "attribute3Code" is not supported by any setter'
            )
        )->during('get', [$attribute3]);
    }
}
