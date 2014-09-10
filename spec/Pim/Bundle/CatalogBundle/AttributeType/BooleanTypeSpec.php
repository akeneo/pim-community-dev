<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormFactory;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Validator\AttributeConstraintGuesser;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

class BooleanTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser, AbstractProductValue $value, AbstractAttribute $isAvailable)
    {
        $value->getAttribute()->willReturn($isAvailable);

        $this->beConstructedWith(AbstractAttributeType::BACKEND_TYPE_BOOLEAN, 'switch', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, $isAvailable)
    {
        $this->buildAttributeFormTypes($factory, $isAvailable)->shouldHaveCount(5);
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
}
