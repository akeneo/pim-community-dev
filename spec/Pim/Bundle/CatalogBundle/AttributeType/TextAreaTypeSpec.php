<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class TextAreaTypeSpec extends ObjectBehavior
{
    function let(ConstraintGuesserInterface $guesser, AttributeInterface $attribute, ProductValueInterface $value)
    {
        $value->getAttribute()->willReturn($attribute);

        $this->beConstructedWith(AbstractAttributeType::BACKEND_TYPE_TEXT, 'textarea', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->getProperties()->willReturn([]);
        $attribute->setProperty(Argument::any(), Argument::any())->shouldBeCalled();

        $this->buildAttributeFormTypes($factory, $attribute)->shouldHaveCount(6);
    }

    function it_prepares_the_product_value_form($value, $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_TEXT);
        $this->prepareValueFormName($value)->shouldReturn(AbstractAttributeType::BACKEND_TYPE_TEXT);
    }

    function it_prepares_the_product_value_form_alias($value, $attribute)
    {
        $attribute->isWysiwygEnabled()->willReturn(true);
        $this->prepareValueFormAlias($value)->shouldReturn('pim_wysiwyg');

        $attribute->isWysiwygEnabled()->willReturn(false);
        $this->prepareValueFormAlias($value)->shouldReturn('textarea');
    }

    function it_prepares_the_product_value_form_options($value, $attribute)
    {
        $attribute->getLabel()->willReturn('name');
        $attribute->isRequired()->willReturn(false);

        $this->prepareValueFormOptions($value)->shouldReturn([
            'label'           => 'name',
            'required'        => false,
            'auto_initialize' => false,
            'label_attr'      => ['truncate' => true]
        ]);
    }

    function it_prepares_the_product_value_form_constraints($value, $attribute, $guesser)
    {
        $guesser->supportAttribute($attribute)->willReturn(true);
        $guesser->guessConstraints($attribute)->willReturn('test');

        $this->prepareValueFormConstraints($value)->shouldReturn([
            'constraints' => 'test'
        ]);
    }

    function it_prepares_default_product_value_form_constraints($value, $attribute, $guesser)
    {
        $guesser->supportAttribute($attribute)->willReturn(false);

        $this->prepareValueFormConstraints($value)->shouldReturn([]);
    }

    function it_prepares_the_product_value_form_data($value)
    {
        $value->getData()->willReturn('my data');
        $this->prepareValueFormData($value)->shouldReturn('my data');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_textarea');
    }
}
