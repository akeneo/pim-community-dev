<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeConstraintGuesser;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class FileTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser, ProductValueInterface $value, AttributeInterface $attribute)
    {
        $value->getAttribute()->willReturn($attribute);

        $this->beConstructedWith(AbstractAttributeType::BACKEND_TYPE_MEDIA, 'pim_enrich_media', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->getProperties()->willReturn([]);
        $attribute->setProperty(Argument::any(), Argument::any())->shouldBeCalled();
        $attribute->getAllowedExtensions()->willReturn(['jpeg', 'gif']);

        $this->buildAttributeFormTypes($factory, $attribute)->shouldHaveCount(6);
    }

    function it_prepares_the_product_value_form_name($value, $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_MEDIA);
        $this->prepareValueFormName($value)->shouldReturn(AbstractAttributeType::BACKEND_TYPE_MEDIA);
    }

    function it_prepares_the_value_form_alias($value)
    {
        $this->getFormType($value)->shouldReturn('pim_enrich_media');
    }

    function it_prepares_the_value_form_options($value, $attribute)
    {
        $attribute->getLabel()->willReturn('myLabel');
        $attribute->isRequired()->willReturn(true);

        $this->prepareValueFormOptions($value)->shouldHaveCount(4);
        $this->prepareValueFormOptions($value)->shouldReturn([
            'label' => 'myLabel',
            'required' => true,
            'auto_initialize' => false,
            'label_attr' => ['truncate' => true]
        ]);
    }

    function it_prepares_the_value_form_constraints($guesser, $value, $attribute)
    {
        $guesser->supportAttribute($attribute)->willReturn(true);
        $guesser->guessConstraints($attribute)->willReturn([]);

        $this->prepareValueFormConstraints($value)->shouldReturn([
            'constraints' => []
        ]);
    }

    function it_prepares_value_form_data($value)
    {
        $value->getData()->willReturn('test');
        $this->prepareValueFormData($value)->shouldReturn('test');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_file');
    }

    function it_is_an_attribute_type()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeInterface');
    }
}
