<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeConstraintGuesser;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class BooleanTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser, ProductValueInterface $value, AttributeInterface $isAvailable)
    {
        $value->getAttribute()->willReturn($isAvailable);

        $this->beConstructedWith(AbstractAttributeType::BACKEND_TYPE_BOOLEAN, 'switch', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, $isAvailable)
    {
        $isAvailable->getId()->willReturn(42);
        $isAvailable->getProperties()->willReturn([]);
        $isAvailable->setProperty(Argument::any(), Argument::any())->shouldBeCalled();
        $this->buildAttributeFormTypes($factory, $isAvailable)->shouldHaveCount(4);
    }

    function it_prepares_the_product_value_form($value, $isAvailable)
    {
        $isAvailable->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_BOOLEAN);
        $this->prepareValueFormName($value)->shouldReturn(AbstractAttributeType::BACKEND_TYPE_BOOLEAN);
    }

    function it_prepares_the_product_value_form_alias($value)
    {
        $this->prepareValueFormAlias($value)->shouldReturn('switch');
    }

    function it_prepares_the_product_value_form_options($value, $isAvailable)
    {
        $isAvailable->getLabel()->willReturn('isAvailable');
        $isAvailable->isRequired()->willReturn(false);

        $this->prepareValueFormOptions($value)->shouldHaveCount(4);
    }

    function it_prepares_the_product_value_form_constraints($value, $isAvailable, $guesser)
    {
        $guesser->supportAttribute($isAvailable)->willReturn(true);
        $guesser->guessConstraints($isAvailable)->willReturn([]);

        $this->prepareValueFormConstraints($value)->shouldHaveCount(1);
    }

    function it_prepares_the_product_value_form_data($value)
    {
        $value->getData()->willReturn(true);
        $this->prepareValueFormData($value)->shouldReturn(true);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_boolean');
    }
}
