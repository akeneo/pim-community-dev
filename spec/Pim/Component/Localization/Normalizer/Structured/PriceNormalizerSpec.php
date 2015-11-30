<?php

namespace spec\Pim\Component\Localization\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PriceNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $priceNormalizer, LocalizerInterface $localizer)
    {
        $this->beConstructedWith($priceNormalizer, $localizer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_attribute_type(ProductPriceInterface $price)
    {
        $this->supportsNormalization($price, 'json')->shouldReturn(true);
        $this->supportsNormalization($price, 'xml')->shouldReturn(true);
        $this->supportsNormalization($price, 'csv')->shouldReturn(false);
        $this->supportsNormalization($price, 'flat')->shouldReturn(false);
    }

    function it_normalizes_price_with_decimal($priceNormalizer, $localizer, ProductPriceInterface $price)
    {
        $options = ['decimal_separator' => ','];
        $price->getData()->willReturn(25.3);
        $price->getCurrency()->willReturn('EUR');

        $data = ['data' => ['EUR' => '25.30']];
        $priceNormalizer->normalize($price, null, $options)->willReturn($data);
        $localizer->localize($data['data'], $options)->willReturn(['EUR' => '25,30']);
        $this->normalize($price, null, $options)->shouldReturn(['data' => ['EUR' => '25,30']]);

        $options = ['decimal_separator' => '.'];
        $priceNormalizer->normalize($price, null, $options)->willReturn($data);
        $localizer->localize($data['data'], $options)->willReturn(['EUR' => '25.30']);
        $this->normalize($price, null, $options)->shouldReturn(['data' => ['EUR' => '25.30']]);
    }

    function it_normalizes_price_without_decimal($priceNormalizer, $localizer, ProductPriceInterface $price)
    {
        $options = ['decimal_separator' => ','];
        $price->getData()->willReturn(25);
        $price->getCurrency()->willReturn('EUR');

        $data = ['data' => ['EUR' => '25']];
        $priceNormalizer->normalize($price, null, $options)->willReturn($data);
        $localizer->localize($data['data'], $options)->willReturn(['EUR' => '25']);
        $this->normalize($price, null, $options)->shouldReturn(['data' => ['EUR' => '25']]);
    }

    function it_normalizes_price_without_decimal_as_string($priceNormalizer, $localizer, ProductPriceInterface $price)
    {
        $options = ['decimal_separator' => ','];
        $price->getData()->willReturn('25');
        $price->getCurrency()->willReturn('EUR');

        $data = ['data' => ['EUR' => '25']];
        $priceNormalizer->normalize($price, null, $options)->willReturn($data);
        $localizer->localize($data['data'], $options)->willReturn(['EUR' => '25']);
        $this->normalize($price, null, $options)->shouldReturn(['data' => ['EUR' => '25']]);
    }

    function it_normalizes_null_price($priceNormalizer, $localizer, ProductPriceInterface $price)
    {
        $options = ['decimal_separator' => ','];
        $price->getData()->willReturn(null);
        $price->getCurrency()->willReturn('EUR');

        $data = ['data' => ['EUR' => '']];
        $priceNormalizer->normalize($price, null, $options)->willReturn($data);
        $localizer->localize($data['data'], $options)->willReturn(['EUR' => '']);
        $this->normalize($price, null, $options)->shouldReturn(['data' => ['EUR' => '']]);
    }

    function it_normalizes_empty_price($priceNormalizer, $localizer, ProductPriceInterface $price)
    {
        $options = ['decimal_separator' => ','];
        $price->getData()->willReturn('');
        $price->getCurrency()->willReturn('EUR');

        $data = ['data' => ['EUR' => '']];
        $priceNormalizer->normalize($price, null, $options)->willReturn($data);
        $localizer->localize($data['data'], $options)->willReturn(['EUR' => '']);
        $this->normalize($price, null, $options)->shouldReturn(['data' => ['EUR' => '']]);
    }
}
