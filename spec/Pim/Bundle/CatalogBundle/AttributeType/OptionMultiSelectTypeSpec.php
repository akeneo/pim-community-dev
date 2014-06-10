<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Validator\AttributeConstraintGuesser;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

class OptionMultiSelectTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser)
    {
        $this->beConstructedWith(AbstractAttributeType::BACKEND_TYPE_OPTIONS, 'pim_ajax_entity', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, AbstractAttribute $color)
    {
        $color->getProperties()->willReturn(['autoOptionSorting' => false]);
        $color->getId()->willReturn(42);

        $this->buildAttributeFormTypes($factory, $color)->shouldHaveCount(7);
    }

    function it_prepares_the_product_value_form(AbstractProductValue $value, AbstractAttribute $color, AttributeOption $red)
    {
        $data = new ArrayCollection([$red]);
        $value->getAttribute()->willReturn($color);
        $value->getData()->willReturn($data);
        $color->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_OPTIONS);
        $this->prepareValueFormName($value)->shouldReturn(AbstractAttributeType::BACKEND_TYPE_OPTIONS);
    }

    function it_prepares_the_product_value_form_alias(AbstractProductValue $value)
    {
        $this->prepareValueFormAlias($value)->shouldReturn('pim_ajax_entity');
    }

    function it_prepares_the_product_value_form_options(AbstractProductValue $value, AbstractAttribute $color)
    {
        $color->getLabel()->willReturn('color');
        $color->isRequired()->willReturn(false);
        $value->getAttribute()->willReturn($color);
        $color->getId()->willReturn(42);
        $color->getMinimumInputLength()->willReturn(42);

        $this->prepareValueFormOptions($value)->shouldHaveCount(8);
    }

    function it_prepares_the_product_value_form_constraints(AbstractProductValue $value, AbstractAttribute $color, $guesser)
    {
        $value->getAttribute()->willReturn($color);
        $guesser->supportAttribute($color)->willReturn(true);
        $guesser->guessConstraints($color)->willReturn([]);

        $this->prepareValueFormConstraints($value)->shouldHaveCount(1);
    }

    function it_prepares_the_product_value_form_data(AbstractProductValue $value, AbstractAttribute $color, AttributeOption $red)
    {
        $data = new ArrayCollection([$red]);
        $value->getData()->willReturn($data);
        $value->getAttribute()->willReturn($color);

        $this->prepareValueFormData($value)->shouldReturnAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');
    }
}
