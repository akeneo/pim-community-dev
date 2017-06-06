<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeConstraintGuesser;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class IdentifierTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser, ProductValueInterface $value, AttributeInterface $sku)
    {
        $value->getAttribute()->willReturn($sku);

        $this->beConstructedWith(AttributeTypes::BACKEND_TYPE_TEXT, 'text', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, $sku)
    {
        $sku->getId()->willReturn(42);
        $sku->getProperties()->willReturn([]);
        $sku->setProperty(Argument::any(), Argument::any())->shouldBeCalled();
        $this->buildAttributeFormTypes($factory, $sku)->shouldHaveCount(7);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_identifier');
    }
}
