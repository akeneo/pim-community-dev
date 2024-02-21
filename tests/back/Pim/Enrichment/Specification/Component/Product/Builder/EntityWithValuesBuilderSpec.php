<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EntityWithValuesBuilderSpec extends ObjectBehavior
{
    function let(
        AttributeValuesResolverInterface $valuesResolver,
        ValueFactory $productValueFactory,
        GetAttributes $getAttributesQuery
    ) {
        $getAttributesQuery->forCode('size')->willReturn(
            new Attribute(
                'size',
                AttributeTypes::OPTION_SIMPLE_SELECT,
                [],
                false,
                false,
                null,
                null,
                false,
                'option',
                []
            )
        );
        $getAttributesQuery->forCode('color')->willReturn(
            new Attribute(
                'color', AttributeTypes::OPTION_SIMPLE_SELECT, [], true, true, null, null, false, 'option', []
            )
        );
        $getAttributesQuery->forCode('label')->willReturn(
            new Attribute('label', AttributeTypes::TEXT, [], true, true, null, null, false, 'option', [])
        );
        $getAttributesQuery->forCode('price')->willReturn(
            new Attribute('price', AttributeTypes::PRICE_COLLECTION, [], false, false, null, null, false, 'prices', [])
        );
        $getAttributesQuery->forCode('weight')->willReturn(
            new Attribute('weight', AttributeTypes::METRIC, [], false, false, null, 'KILOGRAM', false, 'metric', [])
        );

        $this->beConstructedWith($valuesResolver, $productValueFactory, $getAttributesQuery);
    }

    function it_removes_a_product_value_if_data_is_empty(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $size,
        AttributeInterface $color,
        ValueInterface $sizeValue,
        ValueInterface $colorValue
    ) {
        $size->getCode()->willReturn('size');
        $color->getCode()->willReturn('color');

        $product->getValue('size', null, null)->willReturn($sizeValue);
        $product->getValue('color', 'en_US', 'ecommerce')->willReturn($colorValue);

        $product->removeValue($sizeValue)->willReturn($product);
        $product->removeValue($colorValue)->willReturn($product);

        $sizeAttribute = new Attribute(
            'size',
            AttributeTypes::OPTION_SIMPLE_SELECT,
            [],
            false,
            false,
            null,
            null,
            false,
            'option',
            []
        );
        $colorAttribute = new Attribute(
            'color',
            AttributeTypes::OPTION_SIMPLE_SELECT,
            [],
            true,
            true,
            null,
            null,
            false,
            'option',
            []
        );
        $productValueFactory->createByCheckingData($sizeAttribute, null, null, null)->willReturn($sizeValue);
        $productValueFactory->createByCheckingData($colorAttribute, 'ecommerce', 'en_US', null)->willReturn(
            $colorValue
        );

        $product->addValue(Argument::any())->shouldNotBecalled();

        $this->addOrReplaceValue($product, $size, null, null, null);
        $this->addOrReplaceValue($product, $color, 'en_US', 'ecommerce', null);
    }

    function it_considers_a_value_made_of_spaces_only_as_empty(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $label,
        ValueInterface $labelValue
    ) {
        $label->getCode()->willReturn('label');
        $product->getValue('label', null, null)->willReturn($labelValue);

        $product->removeValue($labelValue)->willReturn($product);

        $productValueFactory->createByCheckingData(Argument::cetera())->shouldNotBeCalled();
        $product->addValue(Argument::any())->shouldNotBecalled();

        $this->addOrReplaceValue($product, $label, null, null, ' ');
    }

    function it_updates_a_non_empty_product_value(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $size,
        AttributeInterface $color,
        ValueInterface $formerSizeValue,
        ValueInterface $formerColorValue,
        ValueInterface $sizeValue,
        ValueInterface $colorValue
    ) {
        $size->getCode()->willReturn('size');
        $color->getCode()->willReturn('color');

        $product->getValue('size', null, null)->willReturn($formerSizeValue);
        $product->getValue('color', 'en_US', 'ecommerce')->willReturn($formerColorValue);

        $sizeAttribute = new Attribute(
            'size',
            AttributeTypes::OPTION_SIMPLE_SELECT,
            [],
            false,
            false,
            null,
            null,
            false,
            'option',
            []
        );
        $colorAttribute = new Attribute(
            'color',
            AttributeTypes::OPTION_SIMPLE_SELECT,
            [],
            true,
            true,
            null,
            null,
            false,
            'option',
            []
        );

        $productValueFactory->createByCheckingData($sizeAttribute, null, null, 'xl')->willReturn($sizeValue);
        $productValueFactory->createByCheckingData($colorAttribute, 'ecommerce', 'en_US', 'red')->willReturn(
            $colorValue
        );

        $formerSizeValue->isEqual($sizeValue)->shouldBeCalled()->willReturn(false);
        $product->removeValue($formerSizeValue)->shouldBeCalled()->willReturn($product);
        $product->addValue($sizeValue)->shouldBeCalled()->willReturn($product);

        $formerColorValue->isEqual($colorValue)->shouldBeCalled()->willReturn(false);
        $product->removeValue($formerColorValue)->shouldBeCalled()->willReturn($product);
        $product->addValue($colorValue)->shouldBeCalled()->willReturn($product);

        $this->addOrReplaceValue($product, $size, null, null, 'xl');
        $this->addOrReplaceValue($product, $color, 'en_US', 'ecommerce', 'red');
    }

    function it_adds_a_product_value_if_there_was_not_a_previous_one(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $label,
        ValueInterface $value
    ) {
        $label->getCode()->willReturn('label');

        $product->getValue('label', null, null)->willReturn(null);

        $product->removeValue(Argument::any())->shouldNotBeCalled();

        $labelAttribute = new Attribute('label', AttributeTypes::TEXT, [], true, true, null, null, false, 'option', []);
        $productValueFactory->createByCheckingData($labelAttribute, null, null, 'foobar')->willReturn($value);

        $product->addValue($value)->willReturn($product);

        $this->addOrReplaceValue($product, $label, null, null, 'foobar');
    }

    function it_filters_empty_prices(
        ValueFactory $productValueFactory,
        ProductInterface $product,
        AttributeInterface $price,
        ValueInterface $formerValue,
        ValueInterface $newValue
    ) {
        $price->getCode()->willReturn('price');

        $product->getValue('price', null, null)->willReturn($formerValue);
        $product->removeValue($formerValue)->shouldBeCalled()->willReturn($product);
        $product->addValue($newValue)->shouldBeCalled();

        $priceData = [
            [
                'amount' => null,
                'currency' => 'EUR',
            ],
            [
                'amount' => 20,
                'currency' => 'USD',
            ],
            [
                'amount' => null,
                'currency' => 'GBP',
            ],
            [
                'amount' => 20,
                'currency' => 'YEN',
            ],
        ];
        $productValueFactory->createByCheckingData(
            new Attribute('price', AttributeTypes::PRICE_COLLECTION, [], false, false, null, null, false, 'prices', []),
            null,
            null,
            Argument::that(function (array $value) {
                return array_values($value) === [
                    ['amount' => 20, 'currency' => 'USD'],
                    ['amount' => 20, 'currency' => 'YEN'],
                ];
            })
        )->shouldBeCalled()->willReturn($newValue);
        $formerValue->isEqual($newValue)->shouldBeCalled()->willReturn(false);

        $this->addOrReplaceValue($product, $price, null, null, $priceData);
    }

    function it_does_not_filter_prices_with_wrong_format(
        ValueFactory $productValueFactory,
        ProductInterface $product,
        AttributeInterface $price,
        ValueInterface $formerValue
    ) {
        $price->getCode()->willReturn('price');
        $product->getValue('price', null, null)->willReturn($formerValue);

        $priceData = [
            [
                'currency' => 'EUR',
            ],
            'invalid_data_type'
        ];

        $exception = new \InvalidArgumentException();
        $productValueFactory->createByCheckingData(
            new Attribute('price', AttributeTypes::PRICE_COLLECTION, [], false, false, null, null, false, 'prices', []),
            null,
            null,
            $priceData
        )->shouldBeCalled()->willThrow($exception);

        $this->shouldThrow($exception)->during('addOrReplaceValue', [$product, $price, null, null, $priceData]);
    }

    function it_filters_empty_measurements(
        ValueFactory $productValueFactory,
        ProductInterface $product,
        AttributeInterface $weight,
        ValueInterface $formerValue
    ) {
        $weight->getCode()->willReturn('weight');

        $product->getValue('weight', null, null)->willReturn($formerValue);
        $product->removeValue($formerValue)->shouldBeCalled()->willReturn($product);
        $product->addValue(Argument::any())->shouldNotBeCalled();
        $productValueFactory->createByCheckingData(Argument::any())->shouldNotBeCalled();

        $this->addOrReplaceValue($product, $weight, null, null, ['amount' => null, 'unit' => 'GRAM']);
    }

    function it_does_not_filter_measurements_with_wrong_format(
        ValueFactory $productValueFactory,
        ProductInterface $product,
        AttributeInterface $metric,
        ValueInterface $formerValue
    ) {
        $metric->getCode()->willReturn('weight');
        $product->getValue('weight', null, null)->willReturn($formerValue);

        $data = [
            'foo' => 'bar',
        ];

        $exception = new \InvalidArgumentException();
        $productValueFactory->createByCheckingData(
            new Attribute('weight', AttributeTypes::METRIC, [], false, false, null, 'KILOGRAM', false, 'metric', []),
            null,
            null,
            $data
        )->shouldBeCalled()->willThrow($exception);

        $this->shouldThrow($exception)->during('addOrReplaceValue', [$product, $metric, null, null, $data]);
    }

    function it_does_not_update_a_value_if_the_former_one_was_equal(
        ValueFactory $productValueFactory,
        ProductInterface $product,
        AttributeInterface $label,
        ValueInterface $formerValue,
        ValueInterface $newValue
    ) {
        $label->getCode()->willReturn('label');
        $product->getValue('label', null, null)->willReturn($formerValue);

        $labelAttribute = new Attribute('label', AttributeTypes::TEXT, [], true, true, null, null, false, 'option', []);
        $productValueFactory->createByCheckingData($labelAttribute, null, null, 'My product label')
                            ->shouldBeCalled()
                            ->willReturn($newValue);
        $formerValue->isEqual($newValue)->shouldBeCalled()->willReturn(true);
        $product->removeValue(Argument::any())->shouldNotBeCalled();
        $product->addValue(Argument::any())->shouldNotBeCalled();

        $this->addOrReplaceValue($product, $label, null, null, 'My product label')->shouldReturn($newValue);
    }
}
