<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\AttributeOptionDenormalizer;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;

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

    function it_denormalizes_attribute_options(ProductValueInterface $productValueInterface, $denormalizer)
    {
        $data = '1,2,3';
        $context['value'] = $productValueInterface;

        $denormalizer->denormalize('1', 'pim_catalog_simpleselect', null, $context)->shouldBeCalled();
        $denormalizer->denormalize('2', 'pim_catalog_simpleselect', null, $context)->shouldBeCalled();
        $denormalizer->denormalize('3', 'pim_catalog_simpleselect', null, $context)->shouldBeCalled();

        $this->denormalize($data, 'className', null, $context);
    }
}
