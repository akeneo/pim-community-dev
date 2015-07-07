<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue\AttributeOptionDenormalizer;
use Prophecy\Argument;

class AttributeOptionsDenormalizerSpec extends ObjectBehavior
{
    function let(AttributeOptionDenormalizer $denormalizer)
    {
        $this->beConstructedWith(
            ['pim_catalog_multiselect'],
            $denormalizer
        );
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_denormalizes_attribute_options($denormalizer, ProductValueInterface $productValueInterface, AttributeInterface $abstractAttribute, AttributeOptionInterface $red, AttributeOptionInterface $blue, AttributeOptionInterface $green)
    {
        $data = '1,2,3';
        $context['value'] = $productValueInterface;

        $denormalizer->denormalize('1', 'pim_catalog_simpleselect', Argument::cetera())->shouldBeCalled()->willReturn($red);
        $denormalizer->denormalize('2', 'pim_catalog_simpleselect', Argument::cetera())->shouldBeCalled()->willReturn($blue);
        $denormalizer->denormalize('3', 'pim_catalog_simpleselect', Argument::cetera())->shouldBeCalled()->willReturn($green);

        $options = $this->denormalize($data, 'className', null, $context);

        $options->get(0)->shouldReturn($red);
        $options->get(1)->shouldReturn($blue);
        $options->get(2)->shouldReturn($green);
    }
}
