<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class PriceCollectionAttributeSetterSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        AttributeValidatorHelper $attrValidatorHelper
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            ['pim_catalog_price_collection']
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\SetterInterface');
    }

    function it_supports_price_collection_attributes(
        AttributeInterface $price_collectionAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $price_collectionAttribute->getAttributeType()->willReturn('pim_catalog_price_collection');
        $this->supportsAttribute($price_collectionAttribute)->shouldReturn(true);

        $textareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supportsAttribute($textareaAttribute)->shouldReturn(false);
    }

    function it_checks_locale_and_scope_when_setting_an_attribute_data(
        $attrValidatorHelper,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductValueInterface $priceValue,
        ProductPriceInterface $price
    ) {
        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $attribute->getCode()->willReturn('price');
        $product->getValue('price', 'fr_FR', 'mobile')->willReturn($priceValue);
        $priceValue->getPrices()->willReturn([$price]);
        $price->setData(null)->shouldBeCalled();

        $data = [['amount' => 123.2, 'currency' => 'EUR']];
        $this->setAttributeData($product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_throws_an_error_if_data_is_not_an_array(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'not an array';

        $this->shouldThrow(
            InvalidArgumentException::arrayExpected('attributeCode', 'setter', 'prices collection', gettype($data))
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_attribute_data_does_not_contain_an_array(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = ['not an array'];

        $this->shouldThrow(
            InvalidArgumentException::arrayOfArraysExpected(
                'attributeCode',
                'setter',
                'prices collection',
                gettype($data)
            )
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_attribute_data_value_does_not_contain_data_key(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = [['not the data key' => 123]];

        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected(
                'attributeCode',
                'amount',
                'setter',
                'prices collection',
                print_r($data, true)
            )
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_attribute_data_value_does_not_contain_currency_key(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = [['amount' => 123, 'not the currency key' => 'euro']];

        $this->shouldThrow(
            InvalidArgumentException::arrayKeyExpected(
                'attributeCode',
                'currency',
                'setter',
                'prices collection',
                print_r($data, true)
            )
        )->during('setAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_sets_a_attribute_data_price_collection_value_to_a_product_value(
        $builder,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductInterface $product4,
        ProductValue $productValue,
        ProductPriceInterface $price
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = [['amount' => 123.2, 'currency' => 'EUR']];

        $attribute->getCode()->willReturn('attributeCode');

        $builder
            ->addProductValue($product2, $attribute, $locale, $scope)
            ->willReturn($productValue);

        $product1->getValue('attributeCode', $locale, $scope)->shouldBeCalled()->willReturn($productValue);
        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);
        $product3->getValue('attributeCode', $locale, $scope)->willReturn($productValue);
        $product4->getValue('attributeCode', $locale, $scope)->willReturn($productValue);
        $productValue->getPrices()->willReturn([$price]);
        $price->setData(null)->shouldBeCalled();

        $builder->addPriceForCurrencyWithData($productValue, 'EUR', 123.2)->shouldBeCalled();
        $this->setattributeData($product1, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->setattributeData($product2, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->setattributeData($product3, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);

        $data = [['amount' => 'foo', 'currency' => 'EUR']];
        $builder->addPriceForCurrencyWithData($productValue, 'EUR', 'foo')->shouldBeCalled();
        $this->setattributeData($product4, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
    }
}
