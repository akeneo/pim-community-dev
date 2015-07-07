<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeConstraintGuesser;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class TextTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser, ProductValueInterface $value, AttributeInterface $name)
    {
        $value->getAttribute()->willReturn($name);

        $this->beConstructedWith(AbstractAttributeType::BACKEND_TYPE_VARCHAR, 'text', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, $name)
    {
        $name->getId()->willReturn(42);
        $name->getProperties()->willReturn([]);
        $name->setProperty(Argument::any(), Argument::any())->shouldBeCalled();
        $this->buildAttributeFormTypes($factory, $name)->shouldHaveCount(7);
    }

    function it_prepares_the_product_value_form($value, $name)
    {
        $name->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
        $this->prepareValueFormName($value)->shouldReturn(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
    }

    function it_prepares_the_product_value_form_alias($value)
    {
        $this->prepareValueFormAlias($value)->shouldReturn('text');
    }

    function it_prepares_the_product_value_form_options($value, $name)
    {
        $name->getLabel()->willReturn('name');
        $name->isRequired()->willReturn(false);

        $this->prepareValueFormOptions($value)->shouldHaveCount(4);
    }

    function it_prepares_the_product_value_form_constraints($value, $name, $guesser)
    {
        $guesser->supportAttribute($name)->willReturn(true);
        $guesser->guessConstraints($name)->willReturn([]);

        $this->prepareValueFormConstraints($value)->shouldHaveCount(1);
    }

    function it_prepares_the_product_value_form_data($value)
    {
        $value->getData()->willReturn('my data');
        $this->prepareValueFormData($value)->shouldReturn('my data');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_text');
    }
}
