<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Validator\AttributeConstraintGuesser;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

class TextTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser) {
        $this->beConstructedWith(AbstractAttributeType::BACKEND_TYPE_VARCHAR, 'text', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, AbstractAttribute $name) {

        $this->buildAttributeFormTypes($factory, $name)->shouldHaveCount(8);
    }

    function it_prepares_the_product_value_form(AbstractProductValue $value, AbstractAttribute $name)
    {
        $value->getAttribute()->willReturn($name);
        $name->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
        $this->prepareValueFormName($value)->shouldReturn(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
    }

    function it_prepares_the_product_value_form_alias(AbstractProductValue $value){
        $this->prepareValueFormAlias($value)->shouldReturn('text');
    }

    function it_prepares_the_product_value_form_options(AbstractProductValue $value, AbstractAttribute $name){
        $name->getLabel()->willReturn('name');
        $name->isRequired()->willReturn(false);
        $value->getAttribute()->willReturn($name);

        $this->prepareValueFormOptions($value)->shouldHaveCount(4);
    }

    function it_prepares_the_product_value_form_constraints(AbstractProductValue $value, AbstractAttribute $name, $guesser){
        $value->getAttribute()->willReturn($name);
        $guesser->supportAttribute($name)->willReturn(true);
        $guesser->guessConstraints($name)->willReturn([]);

        $this->prepareValueFormConstraints($value)->shouldHaveCount(1);
    }

    function it_prepares_the_product_value_form_datas(AbstractProductValue $value, AbstractAttribute $name){
        $value->getData()->willReturn('my data');
        $this->prepareValueFormData($value)->shouldReturn('my data');
    }
}
