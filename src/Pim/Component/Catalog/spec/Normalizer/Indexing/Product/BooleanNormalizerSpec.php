<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductValue\BooleanNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BooleanNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BooleanNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_boolean_product_value(
        ValueInterface $textValue,
        ValueInterface $booleanValue,
        AttributeInterface $textAttribute,
        AttributeInterface $booleanAttribute
    ) {
        $textValue->getAttribute()->willReturn($textAttribute);
        $textAttribute->getBackendType()->willReturn('text');

        $booleanValue->getAttribute()->willReturn($booleanAttribute);
        $booleanAttribute->getBackendType()->willReturn('boolean');

        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($booleanValue, 'indexing')->shouldReturn(true);
        $this->supportsNormalization($textValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($textValue, 'indexing')->shouldReturn(false);
    }

    function it_normalizes_a_boolean_product_value_with_no_locale_and_no_channel(
        ValueInterface $mediaValue,
        AttributeInterface $mediaAttribute
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $mediaValue->getLocale()->willReturn(null);
        $mediaValue->getScope()->willReturn(null);
        $mediaValue->getData()->willReturn(true);

        $mediaAttribute->getCode()->willReturn('a_yes_no');
        $mediaAttribute->getBackendType()->willReturn('boolean');

        $this->normalize($mediaValue, 'indexing')->shouldReturn([
            'a_yes_no-boolean' => [
                '<all_channels>' => [
                    '<all_locales>' => true
                ]
            ]
        ]);
    }

    function it_normalizes_a_boolean_product_value_with_locale_and_no_scope(
        ValueInterface $mediaValue,
        AttributeInterface $mediaAttribute
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $mediaValue->getLocale()->willReturn('fr_FR');
        $mediaValue->getScope()->willReturn(null);
        $mediaValue->getData()->willReturn(true);

        $mediaAttribute->getCode()->willReturn('a_yes_no');
        $mediaAttribute->getBackendType()->willReturn('boolean');

        $this->normalize($mediaValue, 'indexing')->shouldReturn([
            'a_yes_no-boolean' => [
                '<all_channels>' => [
                    'fr_FR' => true
                ]
            ]
        ]);
    }

    function it_normalizes_a_boolean_product_value_with_scope_and_no_locale(
        ValueInterface $mediaValue,
        AttributeInterface $mediaAttribute
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $mediaValue->getLocale()->willReturn(null);
        $mediaValue->getScope()->willReturn('ecommerce');
        $mediaValue->getData()->willReturn(true);

        $mediaAttribute->getCode()->willReturn('a_yes_no');
        $mediaAttribute->getBackendType()->willReturn('boolean');

        $this->normalize($mediaValue, 'indexing')->shouldReturn([
            'a_yes_no-boolean' => [
                'ecommerce' => [
                    '<all_locales>' => true
                ]
            ]
        ]);
    }

    function it_normalizes_a_boolean_product_value_with_locale_and_scope(
        ValueInterface $mediaValue,
        AttributeInterface $mediaAttribute
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $mediaValue->getLocale()->willReturn('fr_FR');
        $mediaValue->getScope()->willReturn('ecommerce');
        $mediaValue->getData()->willReturn(true);

        $mediaAttribute->getCode()->willReturn('a_yes_no');
        $mediaAttribute->getBackendType()->willReturn('boolean');

        $this->normalize($mediaValue, 'indexing')->shouldReturn([
            'a_yes_no-boolean' => [
                'ecommerce' => [
                    'fr_FR' => true
                ]
            ]
        ]);
    }

    function it_normalizes_an_empty_boolean_product_value(
        ValueInterface $mediaValue,
        AttributeInterface $mediaAttribute
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $mediaValue->getLocale()->willReturn(null);
        $mediaValue->getScope()->willReturn(null);
        $mediaValue->getData()->willReturn(null);

        $mediaAttribute->getCode()->willReturn('a_yes_no');
        $mediaAttribute->getBackendType()->willReturn('boolean');

        $this->normalize($mediaValue, 'indexing')->shouldReturn([
            'a_yes_no-boolean' => [
                '<all_channels>' => [
                    '<all_locales>' => null
                ]
            ]
        ]);
    }
}
