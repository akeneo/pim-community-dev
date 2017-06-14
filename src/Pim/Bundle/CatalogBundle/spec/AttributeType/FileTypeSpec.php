<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Validator\AttributeConstraintGuesser;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class FileTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser, ValueInterface $value, AttributeInterface $attribute)
    {
        $value->getAttribute()->willReturn($attribute);

        $this->beConstructedWith(AttributeTypes::BACKEND_TYPE_MEDIA, 'pim_enrich_media', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->getProperties()->willReturn([]);
        $attribute->setProperty(Argument::any(), Argument::any())->shouldBeCalled();
        $attribute->getAllowedExtensions()->willReturn(['jpeg', 'gif']);

        $this->buildAttributeFormTypes($factory, $attribute)->shouldHaveCount(6);
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
