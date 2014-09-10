<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Validator\AttributeConstraintGuesser;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

class OptionMultiSelectTypeSpec extends ObjectBehavior
{
    function let(
        AttributeConstraintGuesser $guesser,
        AbstractProductValue $value,
        AbstractAttribute $color,
        AttributeOption $red
    ) {
        $value->getAttribute()->willReturn($color);
        $value->getData()->willReturn(new ArrayCollection([$red]));
        $color->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_OPTIONS);

        $this->beConstructedWith(AbstractAttributeType::BACKEND_TYPE_OPTIONS, 'pim_ajax_entity', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, $color)
    {
        $color->getProperties()->willReturn(['autoOptionSorting' => false]);
        $color->getId()->willReturn(42);

        $this->buildAttributeFormTypes($factory, $color)->shouldHaveCount(7);
    }

    function it_prepares_the_product_value_form($value)
    {
        $this->prepareValueFormName($value)->shouldReturn(AbstractAttributeType::BACKEND_TYPE_OPTIONS);
    }

    function it_prepares_the_product_value_form_alias($value)
    {
        $this->prepareValueFormAlias($value)->shouldReturn('pim_ajax_entity');
    }

    function it_prepares_the_product_value_form_options($value, $color)
    {
        $color->getLabel()->willReturn('color');
        $color->isRequired()->willReturn(false);
        $color->getId()->willReturn(42);
        $color->getMinimumInputLength()->willReturn(42);

        $this->prepareValueFormOptions($value)->shouldHaveCount(8);
    }

    function it_prepares_the_product_value_form_constraints($value, $color, $guesser)
    {
        $guesser->supportAttribute($color)->willReturn(true);
        $guesser->guessConstraints($color)->willReturn([]);

        $this->prepareValueFormConstraints($value)->shouldHaveCount(1);
    }

    function it_prepares_the_product_value_form_data($value, $color)
    {
        $color->getProperty('autoOptionSorting')->willReturn(false);

        $this->prepareValueFormData($value)->shouldReturnAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');
    }
}
