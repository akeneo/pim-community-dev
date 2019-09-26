<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\PriceCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PriceCollectionNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
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
        AttributeInterface $priceCollectionAttribute,
        AttributeInterface $textAttribute,
        $attributeRepository
    ) {
        $textValue->getAttributeCode()->willReturn('my_text_attribute');
        $priceCollectionValue->getAttributeCode()->willReturn('my_prices_attribute');

        $textAttribute->getBackendType()->willReturn('text');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');

        $attributeRepository->findOneByIdentifier('my_text_attribute')->willReturn($textAttribute);
        $attributeRepository->findOneByIdentifier('my_prices_attribute')->willReturn($priceCollectionAttribute);

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
        AttributeInterface $priceCollectionAttribute,
        $attributeRepository
    ) {

        $priceCollection->getAttributeCode()->willReturn('a_price');
        $priceCollection->getLocaleCode()->willReturn(null);
        $priceCollection->getScopeCode()->willReturn(null);
        $priceCollection->getData()->willReturn(null);

        $priceCollectionAttribute->getCode()->willReturn('a_price');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');
        $attributeRepository->findOneByIdentifier('a_price')->willReturn($priceCollectionAttribute);

        $this->normalize($priceCollection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
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
        AttributeInterface $priceCollectionAttribute,
        $attributeRepository
    ) {
        $priceEUR->getData()->willReturn(150.150129);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(12);
        $priceUSD->getCurrency()->willReturn('USD');

        $priceCollection->getAttributeCode()->willReturn('a_price');
        $priceCollection->getLocaleCode()->willReturn(null);
        $priceCollection->getScopeCode()->willReturn(null);
        $prices = new PriceCollection([$priceEUR->getWrappedObject(), $priceUSD->getWrappedObject()]);
        $priceCollection->getData()->willReturn($prices);

        $priceCollectionAttribute->getCode()->willReturn('a_price');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');
        $attributeRepository->findOneByIdentifier('a_price')->willReturn($priceCollectionAttribute);

        $this->normalize($priceCollection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
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
        AttributeInterface $priceCollectionAttribute,
        $attributeRepository
    ) {
        $priceEUR->getData()->willReturn(-150.150129);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(-12);
        $priceUSD->getCurrency()->willReturn('USD');

        $priceCollection->getAttributeCode()->willReturn('a_price');
        $priceCollection->getLocaleCode()->willReturn(null);
        $priceCollection->getScopeCode()->willReturn(null);
        $prices = new PriceCollection([$priceEUR->getWrappedObject(), $priceUSD->getWrappedObject()]);
        $priceCollection->getData()->willReturn($prices);

        $priceCollectionAttribute->getCode()->willReturn('a_price');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');
        $attributeRepository->findOneByIdentifier('a_price')->willReturn($priceCollectionAttribute);

        $this->normalize($priceCollection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
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
        AttributeInterface $priceCollectionAttribute,
        $attributeRepository
    ) {
        $priceEUR->getData()->willReturn(150.150129);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(12);
        $priceUSD->getCurrency()->willReturn('USD');

        $priceCollection->getAttributeCode()->willReturn('a_price');
        $priceCollection->getLocaleCode()->willReturn('fr_FR');
        $priceCollection->getScopeCode()->willReturn(null);
        $prices = new PriceCollection([$priceEUR->getWrappedObject(), $priceUSD->getWrappedObject()]);
        $priceCollection->getData()->willReturn($prices);

        $priceCollectionAttribute->getCode()->willReturn('a_price');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');
        $attributeRepository->findOneByIdentifier('a_price')->willReturn($priceCollectionAttribute);

        $this->normalize($priceCollection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
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
        AttributeInterface $priceCollectionAttribute,
        $attributeRepository
    ) {
        $priceEUR->getData()->willReturn(150.150129);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(12);
        $priceUSD->getCurrency()->willReturn('USD');

        $priceCollection->getAttributeCode()->willReturn('a_price');
        $priceCollection->getLocaleCode()->willReturn(null);
        $priceCollection->getScopeCode()->willReturn('ecommerce');
        $prices = new PriceCollection([$priceEUR->getWrappedObject(), $priceUSD->getWrappedObject()]);
        $priceCollection->getData()->willReturn($prices);

        $priceCollectionAttribute->getCode()->willReturn('a_price');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');
        $attributeRepository->findOneByIdentifier('a_price')->willReturn($priceCollectionAttribute);

        $this->normalize($priceCollection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
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
        AttributeInterface $priceCollectionAttribute,
        $attributeRepository
    ) {
        $priceEUR->getData()->willReturn(150.150129);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(12);
        $priceUSD->getCurrency()->willReturn('USD');

        $priceCollection->getAttributeCode()->willReturn('a_price');
        $priceCollection->getLocaleCode()->willReturn('fr_FR');
        $priceCollection->getScopeCode()->willReturn('ecommerce');
        $prices = new PriceCollection([$priceEUR->getWrappedObject(), $priceUSD->getWrappedObject()]);
        $priceCollection->getData()->willReturn($prices);

        $priceCollectionAttribute->getCode()->willReturn('a_price');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');
        $attributeRepository->findOneByIdentifier('a_price')->willReturn($priceCollectionAttribute);

        $this->normalize($priceCollection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
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
        AttributeInterface $priceCollectionAttribute,
        $attributeRepository
    ) {
        $priceEUR->getData()->willReturn(150.150129);
        $priceEUR->getCurrency()->willReturn('');
        $priceUSD->getData()->willReturn(12);
        $priceUSD->getCurrency()->willReturn(null);

        $priceCollection->getAttributeCode()->willReturn('a_price');
        $priceCollection->getLocaleCode()->willReturn('fr_FR');
        $priceCollection->getScopeCode()->willReturn('ecommerce');
        $prices = new PriceCollection([$priceEUR->getWrappedObject(), $priceUSD->getWrappedObject()]);
        $priceCollection->getData()->willReturn($prices);

        $priceCollectionAttribute->getCode()->willReturn('a_price');
        $priceCollectionAttribute->getBackendType()->willReturn('prices');
        $attributeRepository->findOneByIdentifier('a_price')->willReturn($priceCollectionAttribute);

        $this->normalize($priceCollection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'a_price-prices' => [
                'ecommerce' => [
                    'fr_FR' => [],
                ],
            ],
        ]);
    }
}
