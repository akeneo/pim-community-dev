<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeConstraintGuesser;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class OptionMultiSelectTypeSpec extends ObjectBehavior
{
    function let(
        AttributeConstraintGuesser $guesser,
        ProductValueInterface $value,
        AttributeInterface $color,
        AttributeOptionInterface $red
    ) {
        $value->getAttribute()->willReturn($color);
        $value->getData()->willReturn(new ArrayCollection([$red]));
        $color->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_OPTIONS);

        $this->beConstructedWith(AttributeTypes::BACKEND_TYPE_OPTIONS, 'pim_ajax_entity', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, $color)
    {
        $color->getProperties()->willReturn(['autoOptionSorting' => false]);
        $color->getId()->willReturn(42);
        $color->setProperty(Argument::any(), Argument::any())->shouldBeCalled();

        $this->buildAttributeFormTypes($factory, $color)->shouldHaveCount(6);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_multiselect');
    }
}
