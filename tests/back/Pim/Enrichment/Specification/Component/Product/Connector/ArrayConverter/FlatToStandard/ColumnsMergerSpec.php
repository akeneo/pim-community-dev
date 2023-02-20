<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Connector\Exception\BusinessArrayConversionException;
use PhpSpec\ObjectBehavior;

class ColumnsMergerSpec extends ObjectBehavior
{
    public function let(AttributeColumnInfoExtractor $fieldExtractor, AssociationColumnsResolver $associationColumnResolver)
    {
        $this->beConstructedWith($fieldExtractor, $associationColumnResolver);
    }

    public function it_does_not_merge_columns_which_does_not_represents_attribute_value(
      $fieldExtractor,
      $associationColumnResolver
    ) {
        $row = [
            'enabled' => '1',
            'categories' => 'tshirt,men',
        ];
        $fieldExtractor->extractColumnInfo('enabled')->willReturn(null);
        $fieldExtractor->extractColumnInfo('categories')->willReturn(null);

        $associationColumnResolver->resolveQuantifiedQuantityAssociationColumns()->willReturn([]);
        $associationColumnResolver->resolveQuantifiedIdentifierAssociationColumns()->willReturn([]);

        $mergedRow = $row;
        $this->merge($row, [])->shouldReturn($mergedRow);
    }

    public function it_does_not_merge_columns_which_represents_text_attribute_value(
        $fieldExtractor,
        AttributeInterface $name
    ) {
        $row = [
            'name-fr_FR' => 'T-shirt super beau',
        ];
        $attributeInfoData = [
            'attribute' => $name,
            'locale_code' => 'fr_FR',
            'scope_code' => null,
            'metric_unit' => null,
        ];
        $fieldExtractor->extractColumnInfo('name-fr_FR')->willReturn($attributeInfoData);
        $name->getBackendType()->willReturn('text');

        $mergedRow = $row;
        $this->merge($row, [])->shouldReturn($mergedRow);
    }

    public function it_does_not_merge_columns_which_represents_metric_attribute_value_in_a_single_column(
        $fieldExtractor,
        AttributeInterface $weight
    ) {
        $row = [
            'weight' => '10 KILOGRAM',
        ];
        $attributeInfoData = [
            'attribute' => $weight,
            'locale_code' => null,
            'scope_code' => null,
            'metric_unit' => null,
        ];
        $fieldExtractor->extractColumnInfo('weight')->willReturn($attributeInfoData);
        $weight->getCode()->willReturn('weight');
        $weight->getBackendType()->willReturn('metric');

        $mergedRow = $row;
        $this->merge($row, [])->shouldReturn($mergedRow);
    }

    public function it_does_not_merge_columns_which_represents_a_localizable_metric_attribute_value_in_a_single_column(
        $fieldExtractor,
        AttributeInterface $weight
    ) {
        $row = [
            'weight-fr_FR' => '10 KILOGRAM',
        ];
        $attributeInfoData = [
            'attribute' => $weight,
            'locale_code' => 'fr_FR',
            'scope_code' => null,
            'metric_unit' => null,
        ];
        $fieldExtractor->extractColumnInfo('weight-fr_FR')->willReturn($attributeInfoData);

        $weight->getCode()->willReturn('weight');
        $weight->getBackendType()->willReturn('metric');

        $mergedRow = $row;
        $this->merge($row, [])->shouldReturn($mergedRow);
    }

    public function it_merges_price_attribute_value_columns(
        $fieldExtractor,
        AttributeInterface $price
    ) {
        $row = [
            'price-EUR' => '10',
            'price-USD' => '',
            'price-CHF' => '14',
        ];
        $attributeInfoEur = [
            'attribute' => $price,
            'locale_code' => null,
            'scope_code' => null,
            'price_currency' => 'EUR',
        ];
        $fieldExtractor->extractColumnInfo('price-EUR')->willReturn($attributeInfoEur);
        $attributeInfoUsd = [
            'attribute' => $price,
            'locale_code' => null,
            'scope_code' => null,
            'price_currency' => 'USD',
        ];
        $fieldExtractor->extractColumnInfo('price-USD')->willReturn($attributeInfoUsd);
        $attributeInfoChf = [
            'attribute' => $price,
            'locale_code' => null,
            'scope_code' => null,
            'price_currency' => 'CHF',
        ];
        $fieldExtractor->extractColumnInfo('price-CHF')->willReturn($attributeInfoChf);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $mergedRow = ['price' => '10 EUR,14 CHF'];
        $this->merge($row, [])->shouldReturn($mergedRow);
    }

    public function it_merges_columns_which_represents_metric_attribute_value_in_two_columns(
        $fieldExtractor,
        AttributeInterface $weight
    ) {
        $row = [
            'weight' => '10',
            'weight-unit' => 'KILOGRAM',
        ];
        $attributeInfoData = [
            'attribute' => $weight,
            'locale_code' => null,
            'scope_code' => null,
            'metric_unit' => null,
        ];
        $fieldExtractor->extractColumnInfo('weight')->willReturn($attributeInfoData);

        $attributeInfoUnit = [
            'attribute' => $weight,
            'locale_code' => null,
            'scope_code' => null,
            'metric_unit' => 'unit',
        ];
        $fieldExtractor->extractColumnInfo('weight-unit')->willReturn($attributeInfoUnit);

        $weight->getCode()->willReturn('weight');
        $weight->getBackendType()->willReturn('metric');

        $mergedRow = ['weight' => '10 KILOGRAM'];
        $this->merge($row, [])->shouldReturn($mergedRow);
    }

    public function it_merges_columns_which_represents_metric_attribute_value_with_scientific_notation_in_two_columns(
        $fieldExtractor,
        AttributeInterface $weight
    ) {
        $row = [
            'weight' => 0.000075,
            'weight-unit' => 'GRAM',
        ];
        $attributeInfoData = [
            'attribute' => $weight,
            'locale_code' => null,
            'scope_code' => null,
            'metric_unit' => null,
        ];
        $fieldExtractor->extractColumnInfo('weight')->willReturn($attributeInfoData);

        $attributeInfoUnit = [
            'attribute' => $weight,
            'locale_code' => null,
            'scope_code' => null,
            'metric_unit' => 'unit',
        ];
        $fieldExtractor->extractColumnInfo('weight-unit')->willReturn($attributeInfoUnit);

        $weight->getCode()->willReturn('weight');
        $weight->getBackendType()->willReturn('metric');

        $mergedRow = ['weight' => '0.000075000000 GRAM'];
        $this->merge($row, [])->shouldReturn($mergedRow);
    }

    public function it_merges_columns_which_represents_metric_attribute_value_with_large_decimal_number(
        $fieldExtractor,
        AttributeInterface $weight
    ) {
        $row = [
            'weight' => 80000.75,
            'weight-unit' => 'GRAM',
        ];
        $attributeInfoData = [
            'attribute' => $weight,
            'locale_code' => null,
            'scope_code' => null,
            'metric_unit' => null,
        ];
        $fieldExtractor->extractColumnInfo('weight')->willReturn($attributeInfoData);

        $attributeInfoUnit = [
            'attribute' => $weight,
            'locale_code' => null,
            'scope_code' => null,
            'metric_unit' => 'unit',
        ];
        $fieldExtractor->extractColumnInfo('weight-unit')->willReturn($attributeInfoUnit);

        $weight->getCode()->willReturn('weight');
        $weight->getBackendType()->willReturn('metric');

        $mergedRow = ['weight' => '80000.750000000000 GRAM'];
        $this->merge($row, [])->shouldReturn($mergedRow);
    }

    public function it_merges_columns_which_represents_metric_attribute_value_with_the_decimal_separator(
        $fieldExtractor,
        AttributeInterface $weight
    ) {
        $row = [
            'weight' => 80000.75,
            'weight-unit' => 'GRAM',
        ];
        $attributeInfoData = [
            'attribute' => $weight,
            'locale_code' => null,
            'scope_code' => null,
            'metric_unit' => null,
        ];
        $fieldExtractor->extractColumnInfo('weight')->willReturn($attributeInfoData);

        $attributeInfoUnit = [
            'attribute' => $weight,
            'locale_code' => null,
            'scope_code' => null,
            'metric_unit' => 'unit',
        ];
        $fieldExtractor->extractColumnInfo('weight-unit')->willReturn($attributeInfoUnit);

        $weight->getCode()->willReturn('weight');
        $weight->getBackendType()->willReturn('metric');

        $mergedRow = ['weight' => '80000,750000000000 GRAM'];
        $this->merge($row, ['decimal_separator' => ','])->shouldReturn($mergedRow);
    }

    public function it_merges_columns_which_represents_a_localizable_metric_attribute_value_in_a_two_columns(
        $fieldExtractor,
        AttributeInterface $weight
    ) {
        $row = [
            'weight-fr_FR' => '10',
            'weight-fr_FR-unit' => 'KILOGRAM',
        ];
        $attributeInfoData = [
            'attribute' => $weight,
            'locale_code' => 'fr_FR',
            'scope_code' => null,
            'metric_unit' => null,
        ];
        $fieldExtractor->extractColumnInfo('weight-fr_FR')->willReturn($attributeInfoData);

        $attributeInfoUnit = [
            'attribute' => $weight,
            'locale_code' => 'fr_FR',
            'scope_code' => null,
            'metric_unit' => 'unit',
        ];
        $fieldExtractor->extractColumnInfo('weight-fr_FR-unit')->willReturn($attributeInfoUnit);

        $weight->getCode()->willReturn('weight');
        $weight->getBackendType()->willReturn('metric');

        $mergedRow = ['weight-fr_FR' => '10 KILOGRAM'];
        $this->merge($row, [])->shouldReturn($mergedRow);
    }

    public function it_does_not_merge_columns_which_represents_a_localizable_price_attribute_value_in_a_single_column(
        $fieldExtractor,
        AttributeInterface $price
    ) {
        $row = [
            'price-fr_FR' => '10 EUR, 24 USD',
        ];
        $attributeInfoData = [
            'attribute' => $price,
            'locale_code' => 'fr_FR',
            'scope_code' => null,
            'price_currency' => null,
        ];
        $fieldExtractor->extractColumnInfo('price-fr_FR')->willReturn($attributeInfoData);
        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');

        $mergedRow = $row;
        $this->merge($row, [])->shouldReturn($mergedRow);
    }

    public function it_does_not_create_price_when_price_is_empty(
        $fieldExtractor,
        AttributeInterface $price
    ) {
        $row = ['price-EUR' => ''];
        $attributeInfoEur = [
            'attribute' => $price,
            'locale_code' => null,
            'scope_code' => null,
            'price_currency' => 'EUR',
        ];
        $fieldExtractor->extractColumnInfo('price-EUR')->willReturn($attributeInfoEur);

        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');

        $this->merge($row, [])->shouldReturn(['price' => '']);
    }

    public function it_merges_columns_which_represents_price_attribute_value_in_many_columns(
        $fieldExtractor,
        AttributeInterface $price
    ) {
        $row = [
            'price-EUR' => '10',
            'price-USD' => 12,
            'price-CHF' => '14',
            'price-ARS' => 12.23,
        ];
        $attributeInfoEur = [
            'attribute' => $price,
            'locale_code' => null,
            'scope_code' => null,
            'price_currency' => 'EUR',
        ];
        $fieldExtractor->extractColumnInfo('price-EUR')->willReturn($attributeInfoEur);

        $attributeInfoUsd = [
            'attribute' => $price,
            'locale_code' => null,
            'scope_code' => null,
            'price_currency' => 'USD',
        ];
        $fieldExtractor->extractColumnInfo('price-USD')->willReturn($attributeInfoUsd);

        $attributeInfoChf = [
            'attribute' => $price,
            'locale_code' => null,
            'scope_code' => null,
            'price_currency' => 'CHF',
        ];
        $fieldExtractor->extractColumnInfo('price-CHF')->willReturn($attributeInfoChf);

        $attributeInfoArs = [
            'attribute' => $price,
            'locale_code' => null,
            'scope_code' => null,
            'price_currency' => 'ARS',
        ];
        $fieldExtractor->extractColumnInfo('price-ARS')->willReturn($attributeInfoArs);

        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');

        $mergedRow = ['price' => '10 EUR,12 USD,14 CHF,12.23 ARS'];
        $this->merge($row, [])->shouldReturn($mergedRow);
    }

    public function it_merges_columns_which_represents_price_attribute_with_decimal_separator(
        $fieldExtractor,
        AttributeInterface $price
    ) {
        $row = [
            'price-EUR' => 10.63,
        ];
        $attributeInfoEur = [
            'attribute' => $price,
            'locale_code' => null,
            'scope_code' => null,
            'price_currency' => 'EUR',
        ];
        $fieldExtractor->extractColumnInfo('price-EUR')->willReturn($attributeInfoEur);

        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');

        $mergedRow = ['price' => '10,63 EUR'];
        $this->merge($row, ['decimal_separator' => ','])->shouldReturn($mergedRow);
    }

    public function it_throws_an_exception_when_an_attribute_price_value_is_a_datetime(
        $fieldExtractor,
        AttributeInterface $price
    ) {
        $row = ['price-USD' => new \DateTimeImmutable('2021-11-22')];

        $attributeInfoUsd = [
            'attribute' => $price,
            'locale_code' => null,
            'scope_code' => null,
            'price_currency' => 'USD',
        ];
        $fieldExtractor->extractColumnInfo('price-USD')->willReturn($attributeInfoUsd);

        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');

        $this->shouldThrow(BusinessArrayConversionException::class)->during('merge', [$row, []]);
    }

    public function it_merges_columns_which_represents_quantified_associations_in_two_columns_with_uuids(
        $fieldExtractor,
        $associationColumnResolver
    ) {
        $row = [
            'PACK-products-quantity' => '10|24',
            'PACK-products' => 'd8ddf845-9dad-46dd-ad38-5eea5c1b179d,3b2571c2-4997-455f-afe0-9abb71b8185c',
        ];
        $fieldExtractor->extractColumnInfo('PACK-products-quantity')->willReturn(null);
        $fieldExtractor->extractColumnInfo('PACK-products')->willReturn(null);

        $associationColumnResolver->resolveQuantifiedQuantityAssociationColumns()->willReturn(['PACK-products-quantity']);
        $associationColumnResolver->resolveQuantifiedIdentifierAssociationColumns()->willReturn(['PACK-products']);

        $mergedRow = [
            'PACK-products' => [
                [
                    'uuid' => 'd8ddf845-9dad-46dd-ad38-5eea5c1b179d',
                    'quantity' => 10,
                ],
                [
                    'uuid' => '3b2571c2-4997-455f-afe0-9abb71b8185c',
                    'quantity' => 24,
                ],
            ],
            'PACK-product_models' => [],
        ];
        $this->merge($row, [])->shouldReturn($mergedRow);
    }

    public function it_merges_columns_which_represents_quantified_associations_in_two_columns_with_identifiers(
        $fieldExtractor,
        $associationColumnResolver
    ) {
        $row = [
            'PACK-products-quantity' => '10|24',
            'PACK-products' => 'my_sku,nice',
        ];
        $fieldExtractor->extractColumnInfo('PACK-products-quantity')->willReturn(null);
        $fieldExtractor->extractColumnInfo('PACK-products')->willReturn(null);

        $associationColumnResolver->resolveQuantifiedQuantityAssociationColumns()->willReturn(['PACK-products-quantity']);
        $associationColumnResolver->resolveQuantifiedIdentifierAssociationColumns()->willReturn(['PACK-products']);

        $mergedRow = [
            'PACK-products' => [
                [
                    'identifier' => 'my_sku',
                    'quantity' => 10,
                ],
                [
                    'identifier' => 'nice',
                    'quantity' => 24,
                ],
            ],
            'PACK-product_models' => [],
        ];
        $this->merge($row, [])->shouldReturn($mergedRow);
    }

    public function it_removes_line_breaks_from_measurements(
        AssociationColumnsResolver $associationColumnResolver,
        AttributeColumnInfoExtractor $fieldExtractor,
        AttributeInterface $weight
    ) {
        $row = [
            'weight' => "10\n",
            "weight-unit" => "CENTIMETER"
        ];

        $fieldExtractor->extractColumnInfo('weight')->willReturn([
            'attribute' => $weight,
            'locale_code' => null,
            'scope_code' => null,
            'metric_unit' => null,
        ]);
        $fieldExtractor->extractColumnInfo('weight-unit')->willReturn([
            'attribute' => $weight,
            'locale_code' => null,
            'scope_code' => null,
            'metric_unit' => 'unit',
        ]);
        $weight->getBackendType()->willReturn('metric');
        $weight->getCode()->willReturn('weight');

        $associationColumnResolver->resolveQuantifiedQuantityAssociationColumns()->willReturn([]);
        $associationColumnResolver->resolveQuantifiedIdentifierAssociationColumns()->willReturn([]);

        $this->merge($row, [])->shouldReturn(['weight' => "10 CENTIMETER"]);
    }

    public function it_throw_an_exception_on_missing_column_for_quantified_association(
        $fieldExtractor,
        $associationColumnResolver
    ): void
    {
        $row = [
            'PACK-products' => 'my_sku,nice',
        ];
        $fieldExtractor->extractColumnInfo('PACK-products-quantity')->willReturn(null);
        $fieldExtractor->extractColumnInfo('PACK-products')->willReturn(null);

        $associationColumnResolver->resolveQuantifiedQuantityAssociationColumns()->willReturn(['PACK-products-quantity']);
        $associationColumnResolver->resolveQuantifiedIdentifierAssociationColumns()->willReturn(['PACK-products']);

        $this
            ->shouldThrow(
                new \LogicException('A "PACK-products-quantity" column is missing for quantified association')
            )
            ->during('merge', [$row, []]);
    }
}
