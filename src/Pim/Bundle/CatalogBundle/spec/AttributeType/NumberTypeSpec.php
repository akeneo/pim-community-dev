<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Validator\ConstraintGuesserInterface;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class NumberTypeSpec extends ObjectBehavior
{
    function let(ConstraintGuesserInterface $guesser, AttributeInterface $attribute, ValueInterface $value)
    {
        $value->getAttribute()->willReturn($attribute);

        $this->beConstructedWith(AttributeTypes::BACKEND_TYPE_DECIMAL, 'pim_number', $guesser);
    }

    function it_builds_attributes_form_types(FormFactory $factory, $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->getProperties()->willReturn([]);
        $attribute->setProperty(Argument::any(), Argument::any())->shouldBeCalled();
        $attribute->isDecimalsAllowed()->willReturn(true);

        $this->buildAttributeFormTypes($factory, $attribute)->shouldHaveCount(8);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_number');
    }
}
