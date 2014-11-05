<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class DateTimeDenormalizerSpec extends ObjectBehavior
{
    function let(AttributeOptionRepository $repository)
    {
        $this->beConstructedWith(
            ['pim_catalog_date'],
            $repository
        );
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_denormalizes_a_date()
    {
        $this->denormalize('11-05-2014', 'className', null, [])->shouldHaveType('DateTime');
    }

    function it_returns_null_if_the_data_is_empty(ProductValueInterface $productValueInterface)
    {
        $this->denormalize('', 'className', null, [])->shouldReturn(null);
        $this->denormalize(null, 'className', null, [])->shouldReturn(null);
    }
}
