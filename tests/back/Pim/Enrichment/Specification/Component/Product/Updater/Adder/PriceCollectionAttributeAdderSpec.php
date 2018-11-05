<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\PriceCollectionAttributeAdder;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PriceCollectionAttributeAdderSpec extends ObjectBehavior
{
    function let(EntityWithValuesBuilderInterface $builder, NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($builder, $normalizer, ['pim_catalog_price_collection']);
    }

    function it_is_an_adder()
    {
        $this->shouldImplement(AdderInterface::class);
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

    function it_throws_an_error_if_data_is_not_an_array(
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'not an array';

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'attributeCode',
                PriceCollectionAttributeAdder::class,
                $data
            )
        )->during('addAttributeData', [$product, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_adds_an_attribute_data_price_collection_value_to_a_product_value(
        $builder,
        $normalizer,
        AttributeInterface $attribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ValueInterface $value,
        PriceCollectionInterface $prices,
        ProductPriceInterface $price1,
        ProductPriceInterface $price2,
        \ArrayIterator $pricesIterator
    ) {
        $locale = 'fr_FR';
        $scope = 'mobile';
        $data = [['amount' => 123.2, 'currency' => 'EUR']];

        $attribute->getCode()->willReturn('attributeCode');

        $product1->getValue('attributeCode', $locale, $scope)->willReturn($value);
        $product2->getValue('attributeCode', $locale, $scope)->willReturn(null);

        $value->getData()->shouldBeCalledTimes(1)->willReturn($prices);
        $prices->getIterator()->willReturn($pricesIterator);
        $pricesIterator->rewind()->shouldBeCalled();
        $pricesIterator->valid()->willReturn(true, true, false);
        $pricesIterator->current()->willReturn($price1, $price2);
        $pricesIterator->next()->shouldBeCalled();

        $normalizer
            ->normalize($price1, 'standard')
            ->willReturn(['amount' => 42, 'currency' => 'USD']);

        $normalizer
            ->normalize($price2, 'standard')
            ->willReturn(['amount' => 4.2, 'currency' => 'EUR']);

        $builder->addOrReplaceValue($product1, $attribute, $locale, $scope, [
            ['amount' => 42, 'currency' => 'USD'],
            ['amount' => 4.2, 'currency' => 'EUR'],
            ['amount' => 123.2, 'currency' => 'EUR'],
        ])->shouldBeCalled();

        $builder->addOrReplaceValue($product2, $attribute, $locale, $scope, $data)->shouldBeCalled();

        $this->addattributeData($product1, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
        $this->addattributeData($product2, $attribute, $data, ['locale' => $locale, 'scope' => $scope]);
    }
}
