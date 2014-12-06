<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class AttributeOptionDenormalizerSpec extends ObjectBehavior
{
    function let(AttributeOptionRepository $repository)
    {
        $this->beConstructedWith(
            ['pim_catalog_simpleselect'],
            $repository
        );
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_denormalizes_attribute_option($repository, ProductValueInterface $productValueInterface, AttributeInterface $color, AttributeOption $red)
    {
        $data = '1';
        $context['value'] = $productValueInterface;

        $productValueInterface->getAttribute()->shouldBeCalled()->willReturn($color);
        $color->getCode()->willReturn('color');

        $repository->findByReference('color.1')->shouldBeCalled()->willReturn($red);

        $this->denormalize($data, 'className', null, $context)->shouldReturn($red);
    }

    function it_returns_null_if_the_data_is_empty(ProductValueInterface $productValueInterface)
    {
        $this->denormalize('', 'className', null, [])->shouldReturn(null);
        $this->denormalize(null, 'className', null, [])->shouldReturn(null);
    }
}
