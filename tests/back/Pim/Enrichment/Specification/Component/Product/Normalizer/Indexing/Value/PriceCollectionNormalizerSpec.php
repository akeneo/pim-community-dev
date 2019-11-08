<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\PriceCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PriceCollectionNormalizerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PriceCollectionNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_price_collection_product_value(
        PriceCollectionValue $priceCollectionValue,
        ValueInterface $textValue,
        GetAttributes $getAttributes
    ) {
        $priceCollectionValue->getAttributeCode()->willReturn('my_prices_attribute');
        $textValue->getAttributeCode()->willReturn('my_text_attribute');

        $getAttributes->forCode('my_prices_attribute')->willReturn(new Attribute(
            'my_prices_attribute',
            'pim_catalog_price_collection',
            [],
            false,
            false,
            null,
            true,
            'prices',
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

        $this->supportsNormalization($priceCollectionValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($textValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(
            $priceCollectionValue,
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->shouldReturn(true);
    }

    function it_normalize_an_empty_price_collection_product_value_with_no_locale_and_no_channel(
        PriceCollectionValue $priceCollection,
        GetAttributes $getAttributes
    ) {

        $priceCollection->getAttributeCode()->willReturn('my_prices_attribute');
        $priceCollection->getLocaleCode()->willReturn(null);
        $priceCollection->getScopeCode()->willReturn(null);
        $priceCollection->getData()->willReturn(null);

        $getAttributes->forCode('my_prices_attribute')->willReturn(new Attribute(
            'my_prices_attribute',
            'pim_catalog_price_collection',
            [],
            false,
            false,
            null,
            true,
            'prices',
            []
        ));

        $this->normalize($priceCollection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_prices_attribute-prices' => [
                '<all_channels>' => [
                    '<all_locales>' => null
                ],
            ],
        ]);
    }

    function it_normalize_my_prices_attribute_collection_product_value_with_no_locale_and_no_channel(
        PriceCollectionValue $priceCollection,
        ProductPrice $priceEUR,
        ProductPrice $priceUSD,
        GetAttributes $getAttributes
    ) {
        $priceEUR->getData()->willReturn(150.150129);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(12);
        $priceUSD->getCurrency()->willReturn('USD');

        $priceCollection->getAttributeCode()->willReturn('my_prices_attribute');
        $priceCollection->getLocaleCode()->willReturn(null);
        $priceCollection->getScopeCode()->willReturn(null);
        $prices = new PriceCollection([$priceEUR->getWrappedObject(), $priceUSD->getWrappedObject()]);
        $priceCollection->getData()->willReturn($prices);

        $getAttributes->forCode('my_prices_attribute')->willReturn(new Attribute(
            'my_prices_attribute',
            'pim_catalog_price_collection',
            [],
            false,
            false,
            null,
            true,
            'prices',
            []
        ));

        $this->normalize($priceCollection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_prices_attribute-prices' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'EUR' => '150.150129',
                        'USD' => '12',
                    ],
                ],
            ],
        ]);
    }

    function it_normalize_my_prices_attribute_collection_product_value_with_negative_amount(
        PriceCollectionValue $priceCollection,
        ProductPrice $priceEUR,
        ProductPrice $priceUSD,
        GetAttributes $getAttributes
    ) {
        $priceEUR->getData()->willReturn(-150.150129);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(-12);
        $priceUSD->getCurrency()->willReturn('USD');

        $priceCollection->getAttributeCode()->willReturn('my_prices_attribute');
        $priceCollection->getLocaleCode()->willReturn(null);
        $priceCollection->getScopeCode()->willReturn(null);
        $prices = new PriceCollection([$priceEUR->getWrappedObject(), $priceUSD->getWrappedObject()]);
        $priceCollection->getData()->willReturn($prices);

        $getAttributes->forCode('my_prices_attribute')->willReturn(new Attribute(
            'my_prices_attribute',
            'pim_catalog_price_collection',
            [],
            false,
            false,
            null,
            true,
            'prices',
            []
        ));

        $this->normalize($priceCollection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_prices_attribute-prices' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'EUR' => '-150.150129',
                        'USD' => '-12',
                    ],
                ],
            ],
        ]);
    }

    function it_normalize_my_prices_attribute_collection_product_value_with_locale(
        PriceCollectionValue $priceCollection,
        ProductPrice $priceEUR,
        ProductPrice $priceUSD,
        GetAttributes $getAttributes
    ) {
        $priceEUR->getData()->willReturn(150.150129);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(12);
        $priceUSD->getCurrency()->willReturn('USD');

        $priceCollection->getAttributeCode()->willReturn('my_prices_attribute');
        $priceCollection->getLocaleCode()->willReturn('fr_FR');
        $priceCollection->getScopeCode()->willReturn(null);
        $prices = new PriceCollection([$priceEUR->getWrappedObject(), $priceUSD->getWrappedObject()]);
        $priceCollection->getData()->willReturn($prices);

        $getAttributes->forCode('my_prices_attribute')->willReturn(new Attribute(
            'my_prices_attribute',
            'pim_catalog_price_collection',
            [],
            false,
            true,
            null,
            true,
            'prices',
            []
        ));

        $this->normalize($priceCollection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_prices_attribute-prices' => [
                '<all_channels>' => [
                    'fr_FR' => [
                        'EUR' => '150.150129',
                        'USD' => '12',
                    ],
                ],
            ],
        ]);
    }

    function it_normalize_my_prices_attribute_collection_product_value_with_channel(
        PriceCollectionValue $priceCollection,
        ProductPrice $priceEUR,
        ProductPrice $priceUSD,
        GetAttributes $getAttributes
    ) {
        $priceEUR->getData()->willReturn(150.150129);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(12);
        $priceUSD->getCurrency()->willReturn('USD');

        $priceCollection->getAttributeCode()->willReturn('my_prices_attribute');
        $priceCollection->getLocaleCode()->willReturn(null);
        $priceCollection->getScopeCode()->willReturn('ecommerce');
        $prices = new PriceCollection([$priceEUR->getWrappedObject(), $priceUSD->getWrappedObject()]);
        $priceCollection->getData()->willReturn($prices);

        $getAttributes->forCode('my_prices_attribute')->willReturn(new Attribute(
            'my_prices_attribute',
            'pim_catalog_price_collection',
            [],
            true,
            false,
            null,
            true,
            'prices',
            []
        ));

        $this->normalize($priceCollection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_prices_attribute-prices' => [
                'ecommerce' => [
                    '<all_locales>' => [
                        'EUR' => '150.150129',
                        'USD' => '12',
                    ],
                ],
            ],
        ]);
    }

    function it_normalize_my_prices_attribute_collection_product_value_with_locale_and_channel(
        PriceCollectionValue $priceCollection,
        ProductPrice $priceEUR,
        ProductPrice $priceUSD,
        GetAttributes $getAttributes
    ) {
        $priceEUR->getData()->willReturn(150.150129);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(12);
        $priceUSD->getCurrency()->willReturn('USD');

        $priceCollection->getAttributeCode()->willReturn('my_prices_attribute');
        $priceCollection->getLocaleCode()->willReturn('fr_FR');
        $priceCollection->getScopeCode()->willReturn('ecommerce');
        $prices = new PriceCollection([$priceEUR->getWrappedObject(), $priceUSD->getWrappedObject()]);
        $priceCollection->getData()->willReturn($prices);

        $getAttributes->forCode('my_prices_attribute')->willReturn(new Attribute(
            'my_prices_attribute',
            'pim_catalog_price_collection',
            [],
            true,
            true,
            null,
            true,
            'prices',
            []
        ));

        $this->normalize($priceCollection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_prices_attribute-prices' => [
                'ecommerce' => [
                    'fr_FR' => [
                        'EUR' => '150.150129',
                        'USD' => '12',
                    ],
                ],
            ],
        ]);
    }

    function it_does_not_normalize_my_prices_attribute_collection_product_value_without_currency(
        PriceCollectionValue $priceCollection,
        ProductPrice $priceEUR,
        ProductPrice $priceUSD,
        GetAttributes $getAttributes
    ) {
        $priceEUR->getData()->willReturn(150.150129);
        $priceEUR->getCurrency()->willReturn('');
        $priceUSD->getData()->willReturn(12);
        $priceUSD->getCurrency()->willReturn(null);

        $priceCollection->getAttributeCode()->willReturn('my_prices_attribute');
        $priceCollection->getLocaleCode()->willReturn('fr_FR');
        $priceCollection->getScopeCode()->willReturn('ecommerce');
        $prices = new PriceCollection([$priceEUR->getWrappedObject(), $priceUSD->getWrappedObject()]);
        $priceCollection->getData()->willReturn($prices);

        $getAttributes->forCode('my_prices_attribute')->willReturn(new Attribute(
            'my_prices_attribute',
            'pim_catalog_price_collection',
            [],
            true,
            true,
            null,
            true,
            'prices',
            []
        ));

        $this->normalize($priceCollection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'my_prices_attribute-prices' => [
                'ecommerce' => [
                    'fr_FR' => [],
                ],
            ],
        ]);
    }
}
