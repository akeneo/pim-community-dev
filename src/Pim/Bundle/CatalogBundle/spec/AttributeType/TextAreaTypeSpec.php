<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\ConstraintGuesserInterface;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class TextAreaTypeSpec extends ObjectBehavior
{
    function let(ConstraintGuesserInterface $guesser, AttributeInterface $attribute, ProductValueInterface $value)
    {
        $value->getAttribute()->willReturn($attribute);

        $this->beConstructedWith(AttributeTypes::BACKEND_TYPE_TEXTAREA, 'textarea', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->getProperties()->willReturn([]);
        $attribute->setProperty(Argument::any(), Argument::any())->shouldBeCalled();

        $this->buildAttributeFormTypes($factory, $attribute)->shouldHaveCount(6);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_textarea');
    }
}
