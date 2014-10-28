<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

class AttributeOptionsDenormalizerSpec extends ObjectBehavior
{
    function let(AttributeOptionRepository $repository)
    {
        $this->beConstructedWith(
            ['pim_catalog_multiselect'],
            $repository
        );
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_denormalizes_attribute_options(AttributeOptionRepository $repository, ProductValueInterface $productValueInterface, AbstractAttribute $abstractAttribute)
    {
        $data = '1,2,3';
        $context['value'] = $productValueInterface;

        $productValueInterface->getAttribute()->shouldBeCalled()->willReturn($abstractAttribute);
        $abstractAttribute->getCode()->willReturn('code');

        $repository->findByReference('code.1')->shouldBeCalled()->willReturn();
        $repository->findByReference('code.2')->shouldBeCalled()->willReturn();
        $repository->findByReference('code.3')->shouldBeCalled()->willReturn();

        $this->denormalize($data, 'className', null, $context);
    }
}
