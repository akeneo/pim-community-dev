<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Validator\AttributeConstraintGuesser;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class MetricTypeSpec extends ObjectBehavior
{
    function let(
        AttributeConstraintGuesser $guesser,
        ValueInterface $value,
        AttributeInterface $size
    ) {
        $value->getAttribute()->willReturn($size);

        $this->beConstructedWith(
            AttributeTypes::BACKEND_TYPE_METRIC,
            'pim_enrich_metric',
            $guesser
        );
    }

    function it_builds_the_attribute_forms(FormFactory $factory, $size)
    {
        $size->getId()->willReturn(42);
        $size->getProperties()->willReturn([]);
        $size->setProperty(Argument::any(), Argument::any())->shouldBeCalled();
        $this->buildAttributeFormTypes($factory, $size)->shouldHaveCount(10);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_metric');
    }
}
