<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use PhpSpec\ObjectBehavior;

class DateTimeDenormalizerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['pim_catalog_date']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\DateTimeDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_date_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_catalog_date', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_catalog_text', 'json')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_date', 'csv')->shouldReturn(false);
    }

    function it_denormalizes_data_into_a_date()
    {
        $date = $this->denormalize('01-01-2015', 'pim_catalog_date', 'json');

        $date->shouldHaveType('\DateTime');

        $date->format('d-m-Y')->shouldBe('01-01-2015');
    }

    function it_returns_null_if_data_is_empty()
    {
        $this->denormalize('', 'pim_catalog_date', 'json')->shouldReturn(null);
        $this->denormalize(null, 'pim_catalog_date', 'json')->shouldReturn(null);
    }
}
