<?php

namespace spec\Pim\Component\Localization\Denormalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class NumberDenormalizerSpec extends ObjectBehavior
{
    function let(DenormalizerInterface $valuesDenormalizer, LocalizerInterface $localizer)
    {
        $this->beConstructedWith($valuesDenormalizer, $localizer, ['pim_catalog_number']);
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_number_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_catalog_number', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_catalog_number', 'csv')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_text', 'json')->shouldReturn(false);
    }

    function it_returns_null_if_data_is_empty()
    {
        $this->denormalize('', 'pim_catalog_number', 'json')->shouldReturn(null);
        $this->denormalize(null, 'pim_catalog_number', 'json')->shouldReturn(null);
        $this->denormalize([], 'pim_catalog_number', 'json')->shouldReturn(null);
    }

    function it_denormalizes_data_into_number_with_english_format(
        $valuesDenormalizer,
        $localizer,
        AttributeInterface $attribute
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_number');
        $options = ['attribute' => $attribute, 'locale' => 'en_US'];

        $valuesDenormalizer->denormalize(3.85, 'Pim\Component\Catalog\Model\ProductValue', 'json', $options)
            ->willReturn(3.85);

        $localizer->localize(3.85, $options)->willReturn(3.85);

        $this->denormalize(3.85, 'Pim\Component\Catalog\Model\ProductValue', 'json', $options)
            ->shouldReturn(3.85);
    }

    function it_denormalizes_data_into_number_with_french_format(
        $valuesDenormalizer,
        $localizer,
        AttributeInterface $attribute
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_number');
        $options = ['attribute' => $attribute, 'locale' => 'fr_FR'];

        $valuesDenormalizer->denormalize(3.85, 'Pim\Component\Catalog\Model\ProductValue', 'json', $options)
            ->willReturn(3.85);

        $localizer->localize(3.85, $options)->willReturn('3,85');

        $this->denormalize(3.85, 'Pim\Component\Catalog\Model\ProductValue', 'json', $options)
            ->shouldReturn('3,85');
    }
}
