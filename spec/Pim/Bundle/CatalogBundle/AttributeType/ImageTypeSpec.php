<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class ImageTypeSpec extends ObjectBehavior
{
    function let(ConstraintGuesserInterface $guesser, AttributeInterface $attribute, ProductValueInterface $value)
    {
        $value->getAttribute()->willReturn($attribute);

        $this->beConstructedWith(AbstractAttributeType::BACKEND_TYPE_MEDIA, 'pim_enrich_image', $guesser);
    }

    function it_builds_attribute_form_types(FormFactory $factory, $attribute)
    {
        $attribute->getId()->willReturn(66);
        $attribute->getAllowedExtensions()->willReturn(['jpg', 'png']);
        $attribute->getProperties()->willReturn([]);
        $attribute->setProperty(Argument::any(), Argument::any())->shouldBeCalled();

        $this->buildAttributeFormTypes($factory, $attribute)->shouldHaveCount(6);
    }

    function it_prepares_value_form_name($attribute, $value)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_MEDIA);
        $this->prepareValueFormName($value)->shouldReturn(AbstractAttributeType::BACKEND_TYPE_MEDIA);
    }

    function it_prepares_value_form_alias($value)
    {
        $this->getFormType()->shouldReturn('pim_enrich_image');
    }

    function it_prepares_value_form_options($value, $attribute)
    {
        $attribute->getLabel()->willReturn('A label');
        $attribute->isRequired()->willReturn(true);

        $this->prepareValueFormOptions($value)->shouldReturn([
            'label' => 'A label',
            'required' => true,
            'auto_initialize' => false,
            'label_attr' => ['truncate' => true]
        ]);
    }

    function it_prepares_value_form_constraints($guesser, $attribute, $value)
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
        $this->getName()->shouldReturn('pim_catalog_image');
    }
}
