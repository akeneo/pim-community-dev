<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Validator\AttributeConstraintGuesser;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class BooleanTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser, ValueInterface $value, AttributeInterface $isAvailable)
    {
        $value->getAttribute()->willReturn($isAvailable);

        $this->beConstructedWith(AttributeTypes::BACKEND_TYPE_BOOLEAN, 'switch', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, $isAvailable)
    {
        $isAvailable->getId()->willReturn(42);
        $isAvailable->getProperties()->willReturn([]);
        $isAvailable->setProperty(Argument::any(), Argument::any())->shouldBeCalled();
        $this->buildAttributeFormTypes($factory, $isAvailable)->shouldHaveCount(4);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_boolean');
    }
}
