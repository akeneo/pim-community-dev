<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\PriceCollectionAttributeAdder;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
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
        EntityWithValuesInterface $product
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

    function it_adds_a_price_collection_value_if_it_does_not_exist_yet(
        EntityWithValuesBuilderInterface $builder,
        AttributeInterface $priceAttribute,
        EntityWithValuesInterface $product
    ) {
        $priceAttribute->getCode()->willReturn('price');
        $product->getValue('price', 'en_US', null)->willReturn(null);

        $builder->addOrReplaceValue(
            $product,
            $priceAttribute,
            'en_US',
            null,
            [['currency' => 'USD', 'amount' => 15.00]]
        )->shouldBeCalled();

        $this->addAttributeData(
            $product,
            $priceAttribute,
            [['currency' => 'USD', 'amount' => 15.00]],
            ['locale' => 'en_US', 'scope' => null]
        );
    }

    function it_adds_a_new_price_to_an_existing_price_collection_value(
        EntityWithValuesBuilderInterface $builder,
        NormalizerInterface $normalizer,
        AttributeInterface $priceAttribute,
        EntityWithValuesInterface $product
    ) {
        $priceAttribute->getCode()->willReturn('price');

        $previousPrice = new ProductPrice(12, 'EUR');
        $product->getValue('price', 'en_US', null)->willReturn(PriceCollectionValue::localizableValue(
            'price',
            new PriceCollection([$previousPrice]),
            'en_US'
        ));

        $normalizer->normalize($previousPrice, 'standard')->shouldBeCalled()->willReturn([
            'amount' => 12.00,
            'currency' => 'EUR',
        ]);

        $builder->addOrReplaceValue(
            $product,
            $priceAttribute,
            'en_US',
            null,
            [
                ['currency' => 'EUR', 'amount' => 12.00],
                ['currency' => 'USD', 'amount' => 15.00],
            ]
        )->shouldBeCalled();

        $this->addAttributeData(
            $product,
            $priceAttribute,
            [['currency' => 'USD', 'amount' => 15.00]],
            ['locale' => 'en_US', 'scope' => null]
        );
    }

    function it_replaces_a_price_in_an_existing_price_collection_value(
        EntityWithValuesBuilderInterface $builder,
        NormalizerInterface $normalizer,
        AttributeInterface $priceAttribute,
        EntityWithValuesInterface $product
    ) {
        $priceAttribute->getCode()->willReturn('price');

        $previousPriceUSD = new ProductPrice(17, 'USD');
        $previousPriceEUR = new ProductPrice(12, 'EUR');
        $product->getValue('price', 'en_US', null)->willReturn(PriceCollectionValue::localizableValue(
            'price',
            new PriceCollection([$previousPriceUSD, $previousPriceEUR]),
            'en_US'
        ));

        $normalizer->normalize($previousPriceUSD)->shouldNotBeCalled();
        $normalizer->normalize($previousPriceEUR, 'standard')->shouldBeCalled()->willReturn(
            [
                'amount' => 12.00,
                'currency' => 'EUR',
            ]
        );

        $builder->addOrReplaceValue(
            $product,
            $priceAttribute,
            'en_US',
            null,
            [
                ['currency' => 'EUR', 'amount' => 12.00],
                ['currency' => 'USD', 'amount' => 15.00],
            ]
        )->shouldBeCalled();

        $this->addAttributeData(
            $product,
            $priceAttribute,
            [['currency' => 'USD', 'amount' => 15.00]],
            ['locale' => 'en_US', 'scope' => null]
        );
    }
}
