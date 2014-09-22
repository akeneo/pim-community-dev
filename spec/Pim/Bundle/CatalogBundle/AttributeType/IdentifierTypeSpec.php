<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormFactory;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Validator\AttributeConstraintGuesser;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

class IdentifierTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser, AbstractProductValue $value, AbstractAttribute $sku)
    {
        $value->getAttribute()->willReturn($sku);

        $this->beConstructedWith(AbstractAttributeType::BACKEND_TYPE_VARCHAR, 'text', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, $sku)
    {
        $this->buildAttributeFormTypes($factory, $sku)->shouldHaveCount(8);
    }

    function it_prepares_the_product_value_form($value, $sku)
    {
        $sku->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
        $this->prepareValueFormName($value)->shouldReturn(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
    }

    function it_prepares_the_product_value_form_alias($value)
    {
        $this->prepareValueFormAlias($value)->shouldReturn('text');
    }

    function it_prepares_the_product_value_form_options($value, $sku)
    {
        $sku->getLabel()->willReturn('sku');
        $sku->isRequired()->willReturn(true);

        $this->prepareValueFormOptions($value)->shouldHaveCount(4);
    }

    function it_prepares_the_product_value_form_constraints($value, $sku, $guesser)
    {
        $guesser->supportAttribute($sku)->willReturn(true);
        $guesser->guessConstraints($sku)->willReturn([]);

        $this->prepareValueFormConstraints($value)->shouldHaveCount(1);
    }

    function it_prepares_the_product_value_form_data($value)
    {
        $value->getData()->willReturn('sku-001');
        $this->prepareValueFormData($value)->shouldReturn('sku-001');
    }
}
