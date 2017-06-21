<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Validator\AttributeConstraintGuesser;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class DateTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser, ValueInterface $value, AttributeInterface $releaseDate)
    {
        $value->getAttribute()->willReturn($releaseDate);

        $this->beConstructedWith(AttributeTypes::BACKEND_TYPE_DATE, 'oro_date', $guesser);
    }

    function it_builds_the_attribute_forms(FormFactory $factory, $releaseDate)
    {
        $releaseDate->getId()->willReturn(42);
        $releaseDate->getProperties()->willReturn([]);
        $releaseDate->setProperty(Argument::any(), Argument::any())->shouldBeCalled();
        $this->buildAttributeFormTypes($factory, $releaseDate)->shouldHaveCount(6);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_date');
    }
}
