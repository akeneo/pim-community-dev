<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value;

use Akeneo\Channel\Component\Query\FindActivatedCurrenciesInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Factory\PriceFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\PriceCollectionValueFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Prophecy\Argument;

class PriceCollectionValueFactorySpec extends ObjectBehavior
{
    function let(PriceFactory $priceFactory, FindActivatedCurrenciesInterface $findActivatedCurrencies)
    {
        $this->beConstructedWith(
            $priceFactory,
            PriceCollectionValue::class,
            'pim_catalog_price_collection',
            $findActivatedCurrencies
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PriceCollectionValueFactory::class);
    }

    function it_supports_price_collection_attribute_type()
    {
        $this->supports('foo')->shouldReturn(false);
        $this->supports('pim_catalog_price_collection')->shouldReturn(true);
    }

    function it_throws_an_exception_when_creating_an_empty_price_collection_product_value(
        $priceFactory,
        AttributeInterface $attribute,
        FindActivatedCurrenciesInterface $findActivatedCurrencies
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('price_collection_attribute');
        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $findActivatedCurrencies->forAllChannels()->willReturn(['EUR', 'USD']);

        $priceFactory->createPrice(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_throws_an_exception_when_creating_a_localizable_and_scopable_empty_price_collection_product_value(
        $priceFactory,
        AttributeInterface $attribute,
        FindActivatedCurrenciesInterface $findActivatedCurrencies
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('price_collection_attribute');
        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $findActivatedCurrencies->forChannel('ecommerce')->willReturn(['EUR', 'USD']);

        $priceFactory->createPrice(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$attribute, null, null, null]);
    }

    function it_creates_a_price_collection_product_value(
        $priceFactory,
        AttributeInterface $attribute,
        ProductPriceInterface $priceEUR,
        ProductPriceInterface $priceUSD,
        FindActivatedCurrenciesInterface $findActivatedCurrencies
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('price_collection_attribute');
        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $priceFactory->createPrice(42, 'EUR')->willReturn($priceEUR);
        $priceFactory->createPrice(63, 'USD')->willReturn($priceUSD);

        $findActivatedCurrencies->forAllChannels()->willReturn(['EUR', 'USD', 'AFA']);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            [['amount' => 42, 'currency' => 'EUR'], ['amount' => 63, 'currency' => 'USD']]
        );

        $productValue->shouldReturnAnInstanceOf(PriceCollectionValue::class);
        $productValue->shouldHaveAttribute('price_collection_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHavePrices();
    }

    function it_sorts_the_prices_collection_by_currency(
        $priceFactory,
        $findActivatedCurrencies,
        AttributeInterface $attribute,
        ProductPriceInterface $priceEUR,
        ProductPriceInterface $priceUSD
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('price_collection_attribute');
        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $priceFactory->createPrice(42, 'EUR')->willReturn($priceEUR);
        $priceFactory->createPrice(63, 'USD')->willReturn($priceUSD);

        $findActivatedCurrencies->forAllChannels()->willReturn(['EUR', 'USD']);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            [['amount' => 63, 'currency' => 'USD'], ['amount' => 42, 'currency' => 'EUR']]
        );

        $productValue->shouldReturnAnInstanceOf(PriceCollectionValue::class);
        $productValue->shouldHaveAttribute('price_collection_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHavePricesSortedByCurrency([$priceEUR, $priceUSD]);
    }

    function it_creates_a_price_collection_product_value_when_multiple_amount_are_specified_for_one_currency(
        $priceFactory,
        AttributeInterface $attribute,
        ProductPriceInterface $priceEUR,
        ProductPriceInterface $priceUSD,
        FindActivatedCurrenciesInterface $findActivatedCurrencies
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('price_collection_attribute');
        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $findActivatedCurrencies->forAllChannels()->willReturn(['EUR', 'USD', 'AFA']);

        $priceFactory->createPrice(42, 'EUR')->willReturn($priceEUR);
        $priceFactory->createPrice(30, 'USD')->shouldNotBeCalled();
        $priceFactory->createPrice(63, 'USD')->shouldBeCalled()->willReturn($priceUSD);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            [
                ['amount' => 30, 'currency' => 'USD'],
                ['amount' => 42, 'currency' => 'EUR'],
                ['amount' => 63, 'currency' => 'USD'],
            ]
        );

        $productValue->shouldReturnAnInstanceOf(PriceCollectionValue::class);
        $productValue->shouldHaveAttribute('price_collection_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHavePrices();
    }

    function it_creates_a_localizable_and_scopable_price_collection_product_value(
        $priceFactory,
        AttributeInterface $attribute,
        ProductPriceInterface $priceEUR,
        ProductPriceInterface $priceUSD,
        FindActivatedCurrenciesInterface $findActivatedCurrencies
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('price_collection_attribute');
        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $findActivatedCurrencies->forChannel('ecommerce')->willReturn(['EUR', 'USD', 'AFA']);

        $priceFactory->createPrice(42, 'EUR')->willReturn($priceEUR);
        $priceFactory->createPrice(63, 'USD')->willReturn($priceUSD);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            [['amount' => 42, 'currency' => 'EUR'], ['amount' => 63, 'currency' => 'USD']]
        );

        $productValue->shouldReturnAnInstanceOf(PriceCollectionValue::class);
        $productValue->shouldHaveAttribute('price_collection_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHavePrices();
    }

    function it_does_not_create_prices_for_non_activated_currencies(
        $priceFactory,
        AttributeInterface $attribute,
        ProductPriceInterface $priceUSD,
        FindActivatedCurrenciesInterface $findActivatedCurrencies
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('price_collection_attribute');
        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $findActivatedCurrencies->forAllChannels()->willReturn(['USD']);

        $priceFactory->createPrice(42, 'EUR')->shouldNotBeCalled();
        $priceFactory->createPrice(63, 'USD')->willReturn($priceUSD);

        $productValue = $this->create(
            $attribute,
            null,
            'en_US',
            [['amount' => 42, 'currency' => 'EUR'], ['amount' => 63, 'currency' => 'USD'], ['amount' => 12, 'currency' => 'AFA']]
        );

        $productValue->shouldReturnAnInstanceOf(PriceCollectionValue::class);
        $productValue->shouldHaveAttribute('price_collection_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldNotBeScopable();
        $productValue->shouldHavePrices();
    }

    function it_does_not_create_prices_for_non_activated_currencies_for_the_channel(
        $priceFactory,
        AttributeInterface $attribute,
        ProductPriceInterface $priceUSD,
        FindActivatedCurrenciesInterface $findActivatedCurrencies
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('price_collection_attribute');
        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $findActivatedCurrencies->forChannel('ecommerce')->willReturn(['USD']);

        $priceFactory->createPrice(42, 'EUR')->shouldNotBeCalled();
        $priceFactory->createPrice(63, 'USD')->willReturn($priceUSD);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            [['amount' => 42, 'currency' => 'EUR'], ['amount' => 63, 'currency' => 'USD'], ['amount' => 12, 'currency' => 'AFA']]
        );

        $productValue->shouldReturnAnInstanceOf(PriceCollectionValue::class);
        $productValue->shouldHaveAttribute('price_collection_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldHavePrices();
    }

    function it_throws_an_exception_if_provided_data_is_not_an_array(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('price_collection_attribute');
        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $exception = InvalidPropertyTypeException::arrayExpected(
            'price_collection_attribute',
            PriceCollectionValueFactory::class,
            'foobar'
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', 'foobar']);
    }

    function it_throws_an_exception_if_provided_data_is_not_an_array_of_array(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('price_collection_attribute');
        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $exception = InvalidPropertyTypeException::arrayOfArraysExpected(
            'price_collection_attribute',
            PriceCollectionValueFactory::class,
            ['foobar']
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', ['foobar']]);
    }

    function it_throws_an_exception_if_provided_data_does_not_contains_an_amount(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('price_collection_attribute');
        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $exception = InvalidPropertyTypeException::arrayKeyExpected(
            'price_collection_attribute',
            'amount',
            PriceCollectionValueFactory::class,
            [['foo' => 42, 'currency' => 'EUR']]
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', [['foo' => 42, 'currency' => 'EUR']]]);
    }

    function it_throws_an_exception_if_provided_data_does_not_contains_a_currency(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('price_collection_attribute');
        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $exception = InvalidPropertyTypeException::arrayKeyExpected(
            'price_collection_attribute',
            'currency',
            PriceCollectionValueFactory::class,
            [['amount' => 42, 'bar' => 'EUR']]
        );

        $this
            ->shouldThrow($exception)
            ->during('create', [$attribute, 'ecommerce', 'en_US', [['amount' => 42, 'bar' => 'EUR']]]);
    }

    public function getMatchers(): array
    {
        return [
            'haveAttribute' => function ($subject, $attributeCode) {
                return $subject->getAttributeCode() === $attributeCode;
            },
            'beLocalizable' => function ($subject) {
                return $subject->isLocalizable();
            },
            'haveLocale'    => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocaleCode();
            },
            'beScopable'    => function ($subject) {
                return $subject->isScopable();
            },
            'haveChannel'   => function ($subject, $channelCode) {
                return $channelCode === $subject->getScopeCode();
            },
            'beEmpty'       => function ($subject) {
                return $subject->getData() instanceof PriceCollection && [] === $subject->getData()->toArray();
            },
            'havePrices'    => function ($subject) {
                return $subject->getData() instanceof PriceCollection && [] !== $subject->getData()->toArray();
            },
            'havePricesSortedByCurrency' => function ($subject, $expectedPrices) {
                $data = $subject->getData();

                if (!$data instanceof PriceCollection) {
                    return false;
                }

                if (count($data) !== count($expectedPrices)) {
                    return false;
                }

                for ($i = 0; $i < count($expectedPrices); $i++) {
                    if ($expectedPrices[$i] !== $data[$i]) {
                        return false;
                    }
                }

                return true;
            },
        ];
    }
}
