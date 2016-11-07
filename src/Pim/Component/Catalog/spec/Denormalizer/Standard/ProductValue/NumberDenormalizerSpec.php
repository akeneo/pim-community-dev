<?php

namespace spec\Pim\Component\Catalog\Denormalizer\Standard\ProductValue;

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
        $this->shouldHaveType('Pim\Component\Catalog\Denormalizer\Standard\ProductValue\NumberDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_basic_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_catalog_number', 'standard')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_catalog_text', 'standard')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_price_collection', 'standard')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_boolean', 'csv')->shouldReturn(false);
    }

    function it_returns_data_without_any_modifications_with_en_US_locale($localizer)
    {
        $context = ['locale' => 'en_US'];
        $localizer->localize(1.1, $context)->willReturn(1.1);
        $this->denormalize(1.1, 'pim_catalog_number', 'standard', $context)->shouldReturn(1.1);
    }

    function it_returns_data_without_any_modifications_with_fr_FR_locale($localizer)
    {
        $context = ['locale' => 'fr_FR'];
        $localizer->localize(1.1, $context)->willReturn('1,1');
        $this->denormalize(1.1, 'pim_catalog_number', 'standard', $context)->shouldReturn('1,1');
    }

    function it_returns_null_if_data_is_an_empty_string()
    {
        $this->denormalize('', 'pim_catalog_number', 'standard')->shouldReturn(null);
    }
}
