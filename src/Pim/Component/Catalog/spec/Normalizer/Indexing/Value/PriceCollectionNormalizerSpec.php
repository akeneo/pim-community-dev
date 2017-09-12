<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Value;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductPrice;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\Value\PriceCollectionNormalizer;
use Pim\Component\Catalog\Value\PriceCollectionValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PriceCollectionNormalizerSpec extends ObjectBehavior
{
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
        AttributeInterface $priceCollectionAttribute,
        AttributeInterface $textAttribute
    ) {
        $textValue->getAttribute()->willReturn($textAttribute);
        $priceCollectionValue->getAttribute()->willReturn($priceCollectionAttribute);

        $textAttribute->getBackendType()->willReturn('text');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');

        $this->supportsNormalization(new \stdClass(), ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($textValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(false);
        $this->supportsNormalization($priceCollectionValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($priceCollectionValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(true);

        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($textValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(
            $priceCollectionValue,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->shouldReturn(true);
    }

    function it_normalize_an_empty_price_collection_product_value_with_no_locale_and_no_channel(
        PriceCollectionValue $priceCollection,
        AttributeInterface $priceCollectionAttribute
    ) {

        $priceCollection->getAttribute()->willReturn($priceCollectionAttribute);
        $priceCollection->getLocale()->willReturn(null);
        $priceCollection->getScope()->willReturn(null);
        $priceCollection->getData()->willReturn(null);

        $priceCollectionAttribute->getCode()->willReturn('a_price');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');

        $this->normalize($priceCollection, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'a_price-prices' => [
                '<all_channels>' => [
                    '<all_locales>' => null
                ],
            ],
        ]);
    }

    function it_normalize_a_price_collection_product_value_with_no_locale_and_no_channel(
        PriceCollectionValue $priceCollection,
        ProductPrice $priceEUR,
        ProductPrice $priceUSD,
        AttributeInterface $priceCollectionAttribute
    ) {
        $priceEUR->getData()->willReturn(150.150129);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(12);
        $priceUSD->getCurrency()->willReturn('USD');

        $priceCollection->getAttribute()->willReturn($priceCollectionAttribute);
        $priceCollection->getLocale()->willReturn(null);
        $priceCollection->getScope()->willReturn(null);
        $priceCollection->getData()->willReturn([$priceEUR, $priceUSD]);

        $priceCollectionAttribute->getCode()->willReturn('a_price');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');

        $this->normalize($priceCollection, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'a_price-prices' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'EUR' => '150.150129',
                        'USD' => '12',
                    ],
                ],
            ],
        ]);
    }

    function it_normalize_a_price_collection_product_value_with_negative_amount(
        PriceCollectionValue $priceCollection,
        ProductPrice $priceEUR,
        ProductPrice $priceUSD,
        AttributeInterface $priceCollectionAttribute
    ) {
        $priceEUR->getData()->willReturn(-150.150129);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(-12);
        $priceUSD->getCurrency()->willReturn('USD');

        $priceCollection->getAttribute()->willReturn($priceCollectionAttribute);
        $priceCollection->getLocale()->willReturn(null);
        $priceCollection->getScope()->willReturn(null);
        $priceCollection->getData()->willReturn([$priceEUR, $priceUSD]);

        $priceCollectionAttribute->getCode()->willReturn('a_price');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');

        $this->normalize($priceCollection, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'a_price-prices' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'EUR' => '-150.150129',
                        'USD' => '-12',
                    ],
                ],
            ],
        ]);
    }

    function it_normalize_a_price_collection_product_value_with_locale(
        PriceCollectionValue $priceCollection,
        ProductPrice $priceEUR,
        ProductPrice $priceUSD,
        AttributeInterface $priceCollectionAttribute
    ) {
        $priceEUR->getData()->willReturn(150.150129);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(12);
        $priceUSD->getCurrency()->willReturn('USD');

        $priceCollection->getAttribute()->willReturn($priceCollectionAttribute);
        $priceCollection->getLocale()->willReturn('fr_FR');
        $priceCollection->getScope()->willReturn(null);
        $priceCollection->getData()->willReturn([$priceEUR, $priceUSD]);

        $priceCollectionAttribute->getCode()->willReturn('a_price');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');

        $this->normalize($priceCollection, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'a_price-prices' => [
                '<all_channels>' => [
                    'fr_FR' => [
                        'EUR' => '150.150129',
                        'USD' => '12',
                    ],
                ],
            ],
        ]);
    }

    function it_normalize_a_price_collection_product_value_with_channel(
        PriceCollectionValue $priceCollection,
        ProductPrice $priceEUR,
        ProductPrice $priceUSD,
        AttributeInterface $priceCollectionAttribute
    ) {
        $priceEUR->getData()->willReturn(150.150129);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(12);
        $priceUSD->getCurrency()->willReturn('USD');

        $priceCollection->getAttribute()->willReturn($priceCollectionAttribute);
        $priceCollection->getLocale()->willReturn(null);
        $priceCollection->getScope()->willReturn('ecommerce');
        $priceCollection->getData()->willReturn([$priceEUR, $priceUSD]);

        $priceCollectionAttribute->getCode()->willReturn('a_price');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');

        $this->normalize($priceCollection, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'a_price-prices' => [
                'ecommerce' => [
                    '<all_locales>' => [
                        'EUR' => '150.150129',
                        'USD' => '12',
                    ],
                ],
            ],
        ]);
    }

    function it_normalize_a_price_collection_product_value_with_locale_and_channel(
        PriceCollectionValue $priceCollection,
        ProductPrice $priceEUR,
        ProductPrice $priceUSD,
        AttributeInterface $priceCollectionAttribute
    ) {
        $priceEUR->getData()->willReturn(150.150129);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(12);
        $priceUSD->getCurrency()->willReturn('USD');

        $priceCollection->getAttribute()->willReturn($priceCollectionAttribute);
        $priceCollection->getLocale()->willReturn('fr_FR');
        $priceCollection->getScope()->willReturn('ecommerce');
        $priceCollection->getData()->willReturn([$priceEUR, $priceUSD]);

        $priceCollectionAttribute->getCode()->willReturn('a_price');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');

        $this->normalize($priceCollection, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'a_price-prices' => [
                'ecommerce' => [
                    'fr_FR' => [
                        'EUR' => '150.150129',
                        'USD' => '12',
                    ],
                ],
            ],
        ]);
    }

    function it_does_not_normalize_a_price_collection_product_value_without_currency(
        PriceCollectionValue $priceCollection,
        ProductPrice $priceEUR,
        ProductPrice $priceUSD,
        AttributeInterface $priceCollectionAttribute
    ) {
        $priceEUR->getData()->willReturn(150.150129);
        $priceEUR->getCurrency()->willReturn('');
        $priceUSD->getData()->willReturn(12);
        $priceUSD->getCurrency()->willReturn(null);

        $priceCollection->getAttribute()->willReturn($priceCollectionAttribute);
        $priceCollection->getLocale()->willReturn('fr_FR');
        $priceCollection->getScope()->willReturn('ecommerce');
        $priceCollection->getData()->willReturn([$priceEUR, $priceUSD]);

        $priceCollectionAttribute->getCode()->willReturn('a_price');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');

        $this->normalize($priceCollection, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'a_price-prices' => [
                'ecommerce' => [
                    'fr_FR' => [],
                ],
            ],
        ]);
    }
}
