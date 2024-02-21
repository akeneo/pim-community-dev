<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\ProductValueNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductValueNormalizer::class);
    }

    function it_support_values(ValueInterface $value)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'storage')->shouldReturn(false);
        $this->supportsNormalization($value, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($value, 'storage')->shouldReturn(true);
    }

    function it_normalizes_simple_values($stdNormalizer, ValueInterface $value)
    {
        $value->getAttributeCode()->willReturn('attribute');

        $stdNormalizer->normalize($value, 'storage', ['context'])->willReturn([
            'scope' => null,
            'locale' => null,
            'data' => 'foo'
        ]);

        $storageValue = [];
        $storageValue['attribute']['<all_channels>']['<all_locales>'] = 'foo';

        $this->normalize($value, 'storage', ['context'])->shouldReturn($storageValue);
    }

    function it_normalizes_scopable_values($stdNormalizer, ValueInterface $value)
    {
        $value->getAttributeCode()->willReturn('attribute');

        $stdNormalizer->normalize($value, 'storage', ['context'])->willReturn([
            'scope' => 'ecommerce',
            'locale' => null,
            'data' => 'foo'
        ]);

        $storageValue = [];
        $storageValue['attribute']['ecommerce']['<all_locales>'] = 'foo';

        $this->normalize($value, 'storage', ['context'])->shouldReturn($storageValue);
    }

    function it_normalizes_localizable_values(
        $stdNormalizer,
        ValueInterface $value
    ) {
        $value->getAttributeCode()->willReturn('attribute');

        $stdNormalizer->normalize($value, 'storage', ['context'])->willReturn([
            'scope' => null,
            'locale' => 'fr',
            'data' => 'foo'
        ]);

        $storageValue = [];
        $storageValue['attribute']['<all_channels>']['fr'] = 'foo';

        $this->normalize($value, 'storage', ['context'])->shouldReturn($storageValue);
    }

    function it_normalizes_scopable_and_localizable_values( $stdNormalizer, ValueInterface $value) {
        $value->getAttributeCode()->willReturn('attribute');

        $stdNormalizer->normalize($value, 'storage', ['context'])->willReturn([
            'scope' => 'ecommerce',
            'locale' => 'fr',
            'data' => 'foo'
        ]);

        $storageValue = [];
        $storageValue['attribute']['ecommerce']['fr'] = 'foo';

        $this->normalize($value, 'storage', ['context'])->shouldReturn($storageValue);
    }
}
