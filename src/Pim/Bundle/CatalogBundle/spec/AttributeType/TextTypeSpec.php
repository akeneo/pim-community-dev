<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeConstraintGuesser;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class TextTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser, ProductValueInterface $value, AttributeInterface $name)
    {
        $value->getAttribute()->willReturn($name);

        $this->beConstructedWith(AttributeTypes::BACKEND_TYPE_TEXT, 'text', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, $name)
    {
        $name->getId()->willReturn(42);
        $name->getProperties()->willReturn([]);
        $name->setProperty(Argument::any(), Argument::any())->shouldBeCalled();
        $this->buildAttributeFormTypes($factory, $name)->shouldHaveCount(7);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_text');
    }
}
