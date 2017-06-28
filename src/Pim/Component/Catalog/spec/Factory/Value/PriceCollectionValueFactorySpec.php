<?php

namespace spec\Pim\Component\Catalog\Factory\Value;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\PriceFactory;
use Pim\Component\Catalog\Factory\Value\PriceCollectionValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\PriceCollection;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Catalog\Value\ScalarValue;
use Prophecy\Argument;

class PriceCollectionValueFactorySpec extends ObjectBehavior
{
    function let(PriceFactory $priceFactory)
    {
        $this->beConstructedWith($priceFactory, ScalarValue::class, 'pim_catalog_price_collection', $priceFactory);
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

    function it_creates_a_empty_price_collection_product_value(
        $priceFactory,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('price_collection_attribute');
        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $priceFactory->createPrice(Argument::cetera())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            null,
            null,
            []
        );

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
        $productValue->shouldHaveAttribute('price_collection_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_price_collection_product_value(
        $priceFactory,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('price_collection_attribute');
        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $priceFactory->createPrice(Argument::cetera())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            []
        );

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
        $productValue->shouldHaveAttribute('price_collection_attribute');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_price_collection_product_value(
        $priceFactory,
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

        $productValue = $this->create(
            $attribute,
            null,
            null,
            [['amount' => 42, 'currency' => 'EUR'], ['amount' => 63, 'currency' => 'USD']]
        );

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
        $productValue->shouldHaveAttribute('price_collection_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHavePrices();
    }

    function it_creates_a_price_collection_product_value_when_multiple_amount_are_specified_for_one_currency(
        $priceFactory,
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

        $priceFactory->createPrice(42, 'EUR')->shouldNotBeCalled();
        $priceFactory->createPrice(null, 'EUR')->shouldBeCalled()->willReturn($priceEUR);
        $priceFactory->createPrice(null, 'USD')->shouldNotBeCalled();
        $priceFactory->createPrice(63, 'USD')->shouldBeCalled()->willReturn($priceUSD);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            [
                ['amount' => null, 'currency' => 'USD'],
                ['amount' => 42, 'currency' => 'EUR'],
                ['amount' => 63, 'currency' => 'USD'],
                ['amount' => null, 'currency' => 'EUR'],
            ]
        );

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
        $productValue->shouldHaveAttribute('price_collection_attribute');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHavePrices();
    }

    function it_creates_a_localizable_and_scopable_price_collection_product_value(
        $priceFactory,
        AttributeInterface $attribute,
        ProductPriceInterface $priceEUR,
        ProductPriceInterface $priceUSD
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('price_collection_attribute');
        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $attribute->getBackendType()->willReturn('prices');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $priceFactory->createPrice(42, 'EUR')->willReturn($priceEUR);
        $priceFactory->createPrice(63, 'USD')->willReturn($priceUSD);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            [['amount' => 42, 'currency' => 'EUR'], ['amount' => 63, 'currency' => 'USD']]
        );

        $productValue->shouldReturnAnInstanceOf(ScalarValue::class);
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

    public function getMatchers()
    {
        return [
            'haveAttribute' => function ($subject, $attributeCode) {
                return $subject->getAttribute()->getCode() === $attributeCode;
            },
            'beLocalizable' => function ($subject) {
                return null !== $subject->getLocale();
            },
            'haveLocale'    => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocale();
            },
            'beScopable'    => function ($subject) {
                return null !== $subject->getScope();
            },
            'haveChannel'   => function ($subject, $channelCode) {
                return $channelCode === $subject->getScope();
            },
            'beEmpty'       => function ($subject) {
                return $subject->getData() instanceof PriceCollection && [] === $subject->getData()->toArray();
            },
            'havePrices'    => function ($subject) {
                return $subject->getData() instanceof PriceCollection && [] !== $subject->getData()->toArray();
            },
        ];
    }
}
