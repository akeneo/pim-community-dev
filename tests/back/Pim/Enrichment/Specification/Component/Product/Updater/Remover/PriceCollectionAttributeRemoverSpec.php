<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Remover;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\PriceCollectionAttributeRemover;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\AttributeRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class PriceCollectionAttributeRemoverSpec extends ObjectBehavior
{
    function let(
        CurrencyRepositoryInterface $currencyRepository,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValidatorHelper $attrValidatorHelper
    ) {
        $this->beConstructedWith(
            $attrValidatorHelper,
            $currencyRepository,
            $entityWithValuesBuilder,
            ['pim_catalog_price_collection']
        );
    }

    function it_is_a_remover()
    {
        $this->shouldImplement(AttributeRemoverInterface::class);
    }

    function it_supports_price_collection_attributes(
        AttributeInterface $priceCollectionAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $priceCollectionAttribute->getType()->willReturn('pim_catalog_price_collection');
        $this->supportsAttribute($priceCollectionAttribute)->shouldReturn(true);

        $textareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $this->supportsAttribute($textareaAttribute)->shouldReturn(false);
    }

    function it_removes_an_attribute_data_price_collection_value_from_an_entity_with_values(
        $currencyRepository,
        $entityWithValuesBuilder,
        AttributeInterface $attribute,
        EntityWithValuesInterface $pen,
        EntityWithValuesInterface $book,
        ValueInterface $priceValue,
        PriceCollectionInterface $priceCollection,
        ProductPriceInterface $priceEUR,
        ProductPriceInterface $priceUSD,
        \ArrayIterator $pricesIterator
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = [['amount' => 123.2, 'currency' => 'EUR'], ['amount' => null, 'currency' => 'USD']];

        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);

        $attribute->getCode()->willReturn('attributeCode');

        $pen->getValue('attributeCode', $locale, $scope)->willReturn($priceValue);
        $book->getValue('attributeCode', $locale, $scope)->willReturn(null);

        $priceValue->getData()->willReturn($priceCollection);

        $priceCollection->getIterator()->willReturn($pricesIterator);
        $pricesIterator->rewind()->shouldBeCalled();
        $pricesIterator->valid()->willReturn(true, true, false);
        $pricesIterator->current()->willReturn($priceEUR, $priceUSD);
        $pricesIterator->next()->shouldBeCalled();

        $priceEUR->getData()->willReturn(123.2);
        $priceEUR->getCurrency()->willReturn('EUR');
        $priceUSD->getData()->willReturn(42);
        $priceUSD->getCurrency()->willReturn('USD');

        $entityWithValuesBuilder
            ->addOrReplaceValue($pen, $attribute, $locale, $scope, [])
            ->shouldBeCalled();

        $entityWithValuesBuilder->addOrReplaceValue($book, Argument::cetera())->shouldNotBeCalled();

        $this->removeAttributeData($pen, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->removeAttributeData($book, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
    }

    function it_throws_an_error_if_data_is_not_an_array(
        AttributeInterface $attribute,
        EntityWithValuesInterface $entityWithValues
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'not an array';

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'attributeCode',
                PriceCollectionAttributeRemover::class,
                $data
            )
        )->during('removeAttributeData', [$entityWithValues, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_attribute_data_does_not_contain_an_array(
        AttributeInterface $attribute,
        EntityWithValuesInterface $entityWithValues
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['not an array'];

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayOfArraysExpected(
                'attributeCode',
                PriceCollectionAttributeRemover::class,
                $data
            )
        )->during('removeAttributeData', [$entityWithValues, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_attribute_data_value_does_not_contain_amount_key(
        AttributeInterface $attribute,
        EntityWithValuesInterface $entityWithValues
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = [['not the data key' => 123]];

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayKeyExpected(
                'attributeCode',
                'amount',
                PriceCollectionAttributeRemover::class,
                $data
            )
        )->during('removeAttributeData', [$entityWithValues, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_attribute_data_value_does_not_contain_currency_key(
        AttributeInterface $attribute,
        EntityWithValuesInterface $entityWithValues
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = [['amount' => 123, 'not the currency key' => 'euro']];

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayKeyExpected(
                'attributeCode',
                'currency',
                PriceCollectionAttributeRemover::class,
                $data
            )
        )->during('removeAttributeData', [$entityWithValues, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_attribute_data_value_does_not_contain_valid_currency(
        $currencyRepository,
        AttributeInterface $attribute,
        EntityWithValuesInterface $entityWithValues
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR', 'USD']);

        $data = [['amount' => 123, 'currency' => 'invalid currency']];

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'attributeCode',
                'currency code',
                'The currency does not exist',
                PriceCollectionAttributeRemover::class,
                'invalid currency'
            )
        )->during('removeAttributeData', [$entityWithValues, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }
}
