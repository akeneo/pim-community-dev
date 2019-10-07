<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;

class ColumnsMergerSpec extends ObjectBehavior
{
    function let(AttributeColumnInfoExtractor $fieldExtractor)
    {
        $this->beConstructedWith($fieldExtractor);
    }

    function it_does_not_merge_columns_which_does_not_represents_attribute_value(
      $fieldExtractor
    ) {
        $row = [
            'enabled' => '1',
            'categories' => 'tshirt,men'
        ];
        $fieldExtractor->extractColumnInfo('enabled')->willReturn(null);
        $fieldExtractor->extractColumnInfo('categories')->willReturn(null);

        $mergedRow = $row;
        $this->merge($row)->shouldReturn($mergedRow);
    }

    function it_does_not_merge_columns_which_represents_text_attribute_value(
        $fieldExtractor,
        AttributeInterface $name
    ) {
        $row = [
            'name-fr_FR' => 'T-shirt super beau'
        ];
        $attributeInfoData = [
            'attribute' => $name,
            'locale_code' => 'fr_FR',
            'scope_code' => null,
            'metric_unit' => null
        ];
        $fieldExtractor->extractColumnInfo('name-fr_FR')->willReturn($attributeInfoData);
        $name->getBackendType()->willReturn('text');

        $mergedRow = $row;
        $this->merge($row)->shouldReturn($mergedRow);
    }

    function it_does_not_merge_columns_which_represents_metric_attribute_value_in_a_single_column(
        $fieldExtractor,
        AttributeInterface $weight
    ) {
        $row = [
            'weight' => '10 KILOGRAM'
        ];
        $attributeInfoData = [
            'attribute' => $weight,
            'locale_code' => null,
            'scope_code' => null,
            'metric_unit' => null
        ];
        $fieldExtractor->extractColumnInfo('weight')->willReturn($attributeInfoData);
        $weight->getCode()->willReturn('weight');
        $weight->getBackendType()->willReturn('metric');

        $mergedRow = $row;
        $this->merge($row)->shouldReturn($mergedRow);
    }

    function it_does_not_merge_columns_which_represents_a_localizable_metric_attribute_value_in_a_single_column(
        $fieldExtractor,
        AttributeInterface $weight
    ) {
        $row = [
            'weight-fr_FR' => '10 KILOGRAM'
        ];
        $attributeInfoData = [
            'attribute' => $weight,
            'locale_code' => 'fr_FR',
            'scope_code' => null,
            'metric_unit' => null
        ];
        $fieldExtractor->extractColumnInfo('weight-fr_FR')->willReturn($attributeInfoData);

        $weight->getCode()->willReturn('weight');
        $weight->getBackendType()->willReturn('metric');

        $mergedRow = $row;
        $this->merge($row)->shouldReturn($mergedRow);
    }

    function it_merges_columns_which_represents_metric_attribute_value_in_two_columns(
        $fieldExtractor,
        AttributeInterface $weight
    ) {
        $row = [
            'weight' => '10',
            'weight-unit' => 'KILOGRAM'
        ];
        $attributeInfoData = [
            'attribute' => $weight,
            'locale_code' => null,
            'scope_code' => null,
            'metric_unit' => null
        ];
        $fieldExtractor->extractColumnInfo('weight')->willReturn($attributeInfoData);

        $attributeInfoUnit = [
            'attribute' => $weight,
            'locale_code' => null,
            'scope_code' => null,
            'metric_unit' => 'unit'
        ];
        $fieldExtractor->extractColumnInfo('weight-unit')->willReturn($attributeInfoUnit);

        $weight->getCode()->willReturn('weight');
        $weight->getBackendType()->willReturn('metric');

        $mergedRow = ['weight' => '10 KILOGRAM'];
        $this->merge($row)->shouldReturn($mergedRow);
    }

    function it_merges_columns_which_represents_a_localizable_metric_attribute_value_in_a_two_columns(
        $fieldExtractor,
        AttributeInterface $weight
    ) {
        $row = [
            'weight-fr_FR' => '10',
            'weight-fr_FR-unit' => 'KILOGRAM'
        ];
        $attributeInfoData = [
            'attribute' => $weight,
            'locale_code' => 'fr_FR',
            'scope_code' => null,
            'metric_unit' => null
        ];
        $fieldExtractor->extractColumnInfo('weight-fr_FR')->willReturn($attributeInfoData);

        $attributeInfoUnit = [
            'attribute' => $weight,
            'locale_code' => 'fr_FR',
            'scope_code' => null,
            'metric_unit' => 'unit'
        ];
        $fieldExtractor->extractColumnInfo('weight-fr_FR-unit')->willReturn($attributeInfoUnit);

        $weight->getCode()->willReturn('weight');
        $weight->getBackendType()->willReturn('metric');

        $mergedRow = ['weight-fr_FR' => '10 KILOGRAM'];
        $this->merge($row)->shouldReturn($mergedRow);
    }

    function it_does_not_merge_columns_which_represents_a_localizable_price_attribute_value_in_a_single_column(
        $fieldExtractor,
        AttributeInterface $price
    ) {
        $row = [
            'price-fr_FR' => '10 EUR, 24 USD'
        ];
        $attributeInfoData = [
            'attribute' => $price,
            'locale_code' => 'fr_FR',
            'scope_code' => null,
            'price_currency' => null
        ];
        $fieldExtractor->extractColumnInfo('price-fr_FR')->willReturn($attributeInfoData);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');

        $mergedRow = $row;
        $this->merge($row)->shouldReturn($mergedRow);
    }

    function it_does_not_create_price_when_price_is_empty(
        $fieldExtractor,
        AttributeInterface $price
    ) {
        $row = ['price-EUR' => ''];
        $attributeInfoEur = [
            'attribute' => $price,
            'locale_code' => null,
            'scope_code' => null,
            'price_currency' => 'EUR'
        ];
        $fieldExtractor->extractColumnInfo('price-EUR')->willReturn($attributeInfoEur);

        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');

        $this->merge($row)->shouldReturn(['price' => '']);
    }

    function it_merges_columns_which_represents_price_attribute_value_in_many_columns(
        $fieldExtractor,
        AttributeInterface $price
    ) {
        $row = [
            'price-EUR' => '10',
            'price-USD' => '12',
            'price-CHF' => '14',
        ];
        $attributeInfoEur = [
            'attribute' => $price,
            'locale_code' => null,
            'scope_code' => null,
            'price_currency' => 'EUR'
        ];
        $fieldExtractor->extractColumnInfo('price-EUR')->willReturn($attributeInfoEur);

        $attributeInfoUsd = [
            'attribute' => $price,
            'locale_code' => null,
            'scope_code' => null,
            'price_currency' => 'USD'
        ];
        $fieldExtractor->extractColumnInfo('price-USD')->willReturn($attributeInfoUsd);

        $attributeInfoChf = [
            'attribute' => $price,
            'locale_code' => null,
            'scope_code' => null,
            'price_currency' => 'CHF'
        ];
        $fieldExtractor->extractColumnInfo('price-CHF')->willReturn($attributeInfoChf);

        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');

        $mergedRow = ['price' => '10 EUR,12 USD,14 CHF'];
        $this->merge($row)->shouldReturn($mergedRow);
    }
}
