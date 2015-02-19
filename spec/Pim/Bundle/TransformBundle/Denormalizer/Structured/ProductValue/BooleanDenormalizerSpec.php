<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use PhpSpec\ObjectBehavior;

class BooleanDenormalizerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['pim_catalog_boolean']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\BooleanDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_boolean_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_catalog_boolean', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_catalog_text', 'json')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_boolean', 'csv')->shouldReturn(false);
    }

    function it_denormalizes_data_into_a_boolean()
    {
        $this->denormalize(1, 'pim_catalog_boolean', 'json')->shouldReturn(true);
        $this->denormalize('1', 'pim_catalog_boolean', 'json')->shouldReturn(true);
        $this->denormalize(0, 'pim_catalog_boolean', 'json')->shouldReturn(false);
        $this->denormalize('0', 'pim_catalog_boolean', 'json')->shouldReturn(false);
        $this->denormalize('foo', 'pim_catalog_boolean', 'json')->shouldReturn(true);
    }

    function it_returns_null_if_data_is_null()
    {
        $this->denormalize(null, 'pim_catalog_boolean', 'json')->shouldReturn(null);
    }
}
