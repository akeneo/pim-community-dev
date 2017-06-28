<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Validator\AttributeConstraintGuesser;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class OptionSimpleSelectTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser, ValueInterface $value, AttributeInterface $attribute)
    {
        $value->getAttribute()->willReturn($attribute);

        $this->beConstructedWith(AttributeTypes::BACKEND_TYPE_OPTION, 'pim_ajax_entity', $guesser);
    }

    function it_builds_attribute_form_types(FormFactory $factory, $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->getProperties()->willReturn([]);
        $attribute->setProperty(Argument::any(), Argument::any())->shouldBeCalled();

        $this->buildAttributeFormTypes($factory, $attribute)->shouldHaveCount(6);
    }

    function it_prepares_value_form_alias($value)
    {
        $this->getFormType()->shouldReturn('pim_ajax_entity');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_simpleselect');
    }
}
