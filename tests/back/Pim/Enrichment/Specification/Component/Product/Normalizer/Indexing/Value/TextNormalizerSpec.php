<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\TextNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TextNormalizerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TextNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_text_product_value(
        ValueInterface $numberValue,
        ValueInterface $textValue,
        GetAttributes $getAttributes
    ) {
        $textValue->getAttributeCode()->willReturn('my_text_attribute');
        $numberValue->getAttributeCode()->willReturn('my_number_attribute');

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
        $getAttributes->forCode('my_number_attribute')->willReturn(new Attribute(
            'my_number_attribute',
            'pim_catalog_number',
            [],
            false,
            false,
            null,
            true,
            'decimal',
            []
        ));

        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($numberValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($textValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($numberValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
    }

    function it_normalizes_a_text_product_value_with_no_locale_and_no_channel(
        ValueInterface $textValue,
        GetAttributes $getAttributes
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn(null);
        $textValue->getScopeCode()->willReturn(null);
        $textValue->getData()->willReturn('a product name');

        $getAttributes->forCode('name')->willReturn(new Attribute(
            'name',
            'pim_catalog_text',
            [],
            false,
            false,
            null,
            true,
            'text',
            []
        ));

        $this->normalize($textValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'name-text' => [
                '<all_channels>' => [
                    '<all_locales>' => 'a product name',
                ],
            ],
        ]);
    }

    function it_keeps_the_string_as_is_during_normalization(
        ValueInterface $textValue,
        GetAttributes $getAttributes
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn(null);
        $textValue->getScopeCode()->willReturn(null);
        $textValue->getData()->willReturn('<h1>My <strong>ProDucT</strong> is awesome</h1>');

        $getAttributes->forCode('name')->willReturn(new Attribute(
            'name',
            'pim_catalog_text',
            [],
            false,
            false,
            null,
            true,
            'text',
            []
        ));

        $this->normalize($textValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'name-text' => [
                '<all_channels>' => [
                    '<all_locales>' => '<h1>My <strong>ProDucT</strong> is awesome</h1>',
                ],
            ],
        ]);
    }

    function it_normalizes_an_empty_text_with_no_locale_and_channel(
        ValueInterface $textValue,
        GetAttributes $getAttributes
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn(null);
        $textValue->getScopeCode()->willReturn(null);
        $textValue->getData()->willReturn(null);

        $getAttributes->forCode('name')->willReturn(new Attribute(
            'name',
            'pim_catalog_text',
            [],
            false,
            false,
            null,
            true,
            'text',
            []
        ));

        $this->normalize($textValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'name-text' => [
                '<all_channels>' => [
                    '<all_locales>' => null,
                ],
            ],
        ]);
    }

    function it_normalizes_a_text_product_value_with_locale_and_no_scope(
        ValueInterface $textValue,
        GetAttributes $getAttributes
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn('fr_FR');
        $textValue->getScopeCode()->willReturn(null);
        $textValue->getData()->willReturn('a product name');

        $getAttributes->forCode('name')->willReturn(new Attribute(
            'name',
            'pim_catalog_text',
            [],
            true,
            false,
            null,
            true,
            'text',
            []
        ));

        $this->normalize($textValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'name-text' => [
                '<all_channels>' => [
                    'fr_FR' => 'a product name',
                ],
            ],
        ]);
    }

    function it_normalizes_a_text_product_value_with_scope_and_no_locale(
        ValueInterface $textValue,
        GetAttributes $getAttributes
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn(null);
        $textValue->getScopeCode()->willReturn('ecommerce');
        $textValue->getData()->willReturn('a product name');

        $getAttributes->forCode('name')->willReturn(new Attribute(
            'name',
            'pim_catalog_text',
            [],
            false,
            true,
            null,
            true,
            'text',
            []
        ));

        $this->normalize($textValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'name-text' => [
                'ecommerce' => [
                    '<all_locales>' => 'a product name',
                ],
            ],
        ]);
    }

    function it_normalizes_a_text_product_value_with_locale_and_scope(
        ValueInterface $textValue,
        GetAttributes $getAttributes
    ) {
        $textValue->getAttributeCode()->willReturn('name');
        $textValue->getLocaleCode()->willReturn('fr_FR');
        $textValue->getScopeCode()->willReturn('ecommerce');
        $textValue->getData()->willReturn('a product name');

        $getAttributes->forCode('name')->willReturn(new Attribute(
            'name',
            'pim_catalog_text',
            [],
            true,
            true,
            null,
            true,
            'text',
            []
        ));

        $this->normalize($textValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'name-text' => [
                'ecommerce' => [
                    'fr_FR' => 'a product name',
                ],
            ],
        ]);
    }
}
