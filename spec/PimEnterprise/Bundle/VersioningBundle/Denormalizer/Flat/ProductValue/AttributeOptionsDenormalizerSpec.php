<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\AttributeOptionDenormalizer;

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

    function it_denormalizes_attribute_options($repository, ProductValueInterface $productValueInterface, AbstractAttribute $abstractAttribute, AttributeOption $red, AttributeOption $blue, AttributeOption $green)
    {
        $data = '1,2,3';
        $context['value'] = $productValueInterface;

        $productValueInterface->getAttribute()->shouldBeCalled()->willReturn($abstractAttribute);
        $abstractAttribute->getCode()->willReturn('color');

        $repository->findByReference('color.1')->shouldBeCalled()->willReturn($red);
        $repository->findByReference('color.2')->shouldBeCalled()->willReturn($blue);
        $repository->findByReference('color.3')->shouldBeCalled()->willReturn($green);

        $options = $this->denormalize($data, 'className', null, $context);

        $options->get(0)->shouldReturn($red);
        $options->get(1)->shouldReturn($blue);
        $options->get(2)->shouldReturn($green);
    }
}
