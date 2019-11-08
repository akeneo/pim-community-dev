<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\BooleanNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BooleanNormalizerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(BooleanNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_boolean_product_value_for_both_indexing_formats(
        ValueInterface $textValue,
        ValueInterface $booleanValue,
        GetAttributes $getAttributes
    ) {
        $textValue->getAttributeCode()->willReturn('my_text_attribute');
        $booleanValue->getAttributeCode()->willReturn('my_boolean_attribute');

        $getAttributes->forCode('my_boolean_attribute')->willReturn(new Attribute(
            'my_boolean_attribute',
            'pim_catalog_boolean',
            [],
            false,
            false,
            null,
            true,
            'boolean',
            []
        ));
        $getAttributes->forCode('my_text_attribute')->willReturn(new Attribute(
            'my_text_attribute',
            'pim_catalog_text',
            [],
            false,
            false,
            null,
            true,
            'text',
            []
        ));

        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization($textValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($booleanValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($textValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
    }

    function it_normalizes_a_boolean_product_value_with_no_locale_and_no_channel(
        ValueInterface $value,
        GetAttributes $getAttributes
    ) {
        $value->getAttributeCode()->willReturn('my_boolean_attribute');
        $getAttributes->forCode('my_boolean_attribute')->willReturn(new Attribute(
            'my_boolean_attribute',
            'pim_catalog_boolean',
            [],
            false,
            false,
            null,
            true,
            'boolean',
            []
        ));

        $value->getLocaleCode()->willReturn(null);
        $value->getScopeCode()->willReturn(null);
        $value->getData()->willReturn(true);

        $this->normalize($value, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_boolean_attribute-boolean' => [
                '<all_channels>' => [
                    '<all_locales>' => true
                ]
            ]
        ]);
    }

    function it_normalizes_a_boolean_product_value_with_locale_and_no_scope(
        ValueInterface $value,
        GetAttributes $getAttributes
    ) {
        $value->getAttributeCode()->willReturn('my_boolean_attribute');
        $getAttributes->forCode('my_boolean_attribute')->willReturn(new Attribute(
            'my_boolean_attribute',
            'pim_catalog_boolean',
            [],
            false,
            false,
            null,
            true,
            'boolean',
            []
        ));

        $value->getLocaleCode()->willReturn('fr_FR');
        $value->getScopeCode()->willReturn(null);
        $value->getData()->willReturn(true);

        $this->normalize($value, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_boolean_attribute-boolean' => [
                '<all_channels>' => [
                    'fr_FR' => true
                ]
            ]
        ]);
    }

    function it_normalizes_a_boolean_product_value_with_scope_and_no_locale(
        ValueInterface $value,
        GetAttributes $getAttributes
    ) {
        $value->getAttributeCode()->willReturn('my_boolean_attribute');
        $getAttributes->forCode('my_boolean_attribute')->willReturn(new Attribute(
            'my_boolean_attribute',
            'pim_catalog_boolean',
            [],
            false,
            false,
            null,
            true,
            'boolean',
            []
        ));

        $value->getLocaleCode()->willReturn(null);
        $value->getScopeCode()->willReturn('ecommerce');
        $value->getData()->willReturn(true);

        $this->normalize($value, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_boolean_attribute-boolean' => [
                'ecommerce' => [
                    '<all_locales>' => true
                ]
            ]
        ]);
    }

    function it_normalizes_a_boolean_product_value_with_locale_and_scope(
        ValueInterface $value,
        GetAttributes $getAttributes
    ) {
        $value->getAttributeCode()->willReturn('my_boolean_attribute');
        $getAttributes->forCode('my_boolean_attribute')->willReturn(new Attribute(
            'my_boolean_attribute',
            'pim_catalog_boolean',
            [],
            false,
            false,
            null,
            true,
            'boolean',
            []
        ));

        $value->getLocaleCode()->willReturn('fr_FR');
        $value->getScopeCode()->willReturn('ecommerce');
        $value->getData()->willReturn(true);

        $this->normalize($value, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_boolean_attribute-boolean' => [
                'ecommerce' => [
                    'fr_FR' => true
                ]
            ]
        ]);
    }

    function it_normalizes_an_empty_boolean_product_value(
        ValueInterface $value,
        GetAttributes $getAttributes
    ) {
        $value->getAttributeCode()->willReturn('my_boolean_attribute');
        $getAttributes->forCode('my_boolean_attribute')->willReturn(new Attribute(
            'my_boolean_attribute',
            'pim_catalog_boolean',
            [],
            false,
            false,
            null,
            true,
            'boolean',
            []
        ));

        $value->getLocaleCode()->willReturn(null);
        $value->getScopeCode()->willReturn(null);
        $value->getData()->willReturn(null);

        $this->normalize($value, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_boolean_attribute-boolean' => [
                '<all_channels>' => [
                    '<all_locales>' => null
                ]
            ]
        ]);
    }
}
