<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use PhpSpec\ObjectBehavior;

class NumberDenormalizerSpec extends ObjectBehavior
{
    function let(LocalizerInterface $localizer)
    {
        $this->beConstructedWith(['pim_catalog_number'], $localizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\NumberDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_basic_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_catalog_number', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_catalog_text', 'json')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_price_collection', 'json')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_boolean', 'csv')->shouldReturn(false);
    }

    function it_returns_data_without_any_modifications_with_en_US_locale($localizer)
    {
        $context = ['locale' => 'en_US'];
        $localizer->localize(1.1, $context)->willReturn(1.1);
        $this->denormalize(1.1, 'pim_catalog_number', 'json', $context)->shouldReturn(1.1);
    }

    function it_returns_data_without_any_modifications_with_fr_FR_locale($localizer)
    {
        $context = ['locale' => 'fr_FR'];
        $localizer->localize(1.1, $context)->willReturn('1,1');
        $this->denormalize(1.1, 'pim_catalog_number', 'json', $context)->shouldReturn('1,1');
    }

    function it_returns_null_if_data_is_an_empty_string()
    {
        $this->denormalize('', 'pim_catalog_number', 'json')->shouldReturn(null);
    }
}
