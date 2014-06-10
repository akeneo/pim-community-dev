<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Validator\AttributeConstraintGuesser;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

class DateTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser)
    {
        $this->beConstructedWith(AbstractAttributeType::BACKEND_TYPE_DATE, 'oro_date', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, AbstractAttribute $releaseDate)
    {
        $this->buildAttributeFormTypes($factory, $releaseDate)->shouldHaveCount(7);
    }

    function it_prepares_the_product_value_form(AbstractProductValue $value, AbstractAttribute $releaseDate)
    {
        $value->getAttribute()->willReturn($releaseDate);
        $releaseDate->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_DATE);
        $this->prepareValueFormName($value)->shouldReturn(AbstractAttributeType::BACKEND_TYPE_DATE);
    }

    function it_prepares_the_product_value_form_alias(AbstractProductValue $value)
    {
        $this->prepareValueFormAlias($value)->shouldReturn('oro_date');
    }

    function it_prepares_the_product_value_form_options(AbstractProductValue $value, AbstractAttribute $releaseDate)
    {
        $releaseDate->getLabel()->willReturn('releaseDate');
        $releaseDate->isRequired()->willReturn(false);
        $value->getAttribute()->willReturn($releaseDate);

        $this->prepareValueFormOptions($value)->shouldHaveCount(6);
    }

    function it_prepares_the_product_value_form_constraints(AbstractProductValue $value, AbstractAttribute $releaseDate, $guesser)
    {
        $value->getAttribute()->willReturn($releaseDate);
        $guesser->supportAttribute($releaseDate)->willReturn(true);
        $guesser->guessConstraints($releaseDate)->willReturn([]);

        $this->prepareValueFormConstraints($value)->shouldHaveCount(1);
    }

    function it_prepares_the_product_value_form_data(AbstractProductValue $value, AbstractAttribute $releaseDate)
    {
        $date = new \DateTime();
        $value->getData()->willReturn($date);
        $this->prepareValueFormData($value)->shouldReturn($date);
    }
}
