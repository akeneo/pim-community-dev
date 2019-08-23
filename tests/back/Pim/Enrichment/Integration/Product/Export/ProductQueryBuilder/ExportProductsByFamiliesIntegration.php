<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByFamiliesIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', ['family' => 'familyA']);
        $this->createProduct('product_2', ['family' => 'familyA1']);
        $this->createProduct('product_3', ['family' => 'familyA2']);
        $this->createProduct('product_4');
    }

    public function testProductExportWithFilterOnOneFamily()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;an_image;a_date;a_file;a_localizable_image-en_US;a_localized_and_scopable_text_area-en_US-tablet;a_metric;a_metric-unit;a_multi_select;a_number_float;a_number_float_negative;a_number_integer;a_price-CNY;a_price-EUR;a_price-USD;a_ref_data_multi_select;a_ref_data_simple_select;a_scopable_price-tablet-EUR;a_scopable_price-tablet-USD;a_simple_select;a_text;a_text_area;a_yes_no
product_1;;1;familyA;;;;;;;;;;;;;;;;;;;;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'family',
                        'operator' => 'IN',
                        'value'    => ['familyA']
                    ],
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
        ];

        $this->assertProductExport($expectedCsv, $config);
    }

    public function testProductExportWithFilterOnAListOfFamilies()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;an_image;a_date;a_file;a_localizable_image-en_US;a_localized_and_scopable_text_area-en_US-tablet;a_metric;a_metric-unit;a_multi_select;a_number_float;a_number_float_negative;a_number_integer;a_price-CNY;a_price-EUR;a_price-USD;a_ref_data_multi_select;a_ref_data_simple_select;a_scopable_price-tablet-EUR;a_scopable_price-tablet-USD;a_simple_select;a_text;a_text_area;a_yes_no
product_1;;1;familyA;;;;;;;;;;;;;;;;;;;;;;;
product_2;;1;familyA1;;;;;;;;;;;;;;;;;;;;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'family',
                        'operator' => 'IN',
                        'value'    => ['familyA', 'familyA1']
                    ],
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
        ];

        $this->assertProductExport($expectedCsv, $config);
    }

    public function testProductExportWithoutAnyFilterOnFamily()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;an_image;a_date;a_file;a_localizable_image-en_US;a_localized_and_scopable_text_area-en_US-tablet;a_metric;a_metric-unit;a_multi_select;a_number_float;a_number_float_negative;a_number_integer;a_price-CNY;a_price-EUR;a_price-USD;a_ref_data_multi_select;a_ref_data_simple_select;a_scopable_price-tablet-EUR;a_scopable_price-tablet-USD;a_simple_select;a_text;a_text_area;a_yes_no
product_1;;1;familyA;;;;;;;;;;;;;;;;;;;;;;;
product_2;;1;familyA1;;;;;;;;;;;;;;;;;;;;;;;
product_3;;1;familyA2;;;;;;;;;;;;;;;;;;;;;;;
product_4;;1;;;;;;;;;;;;;;;;;;;;;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
        ];

        $this->assertProductExport($expectedCsv, $config);
    }
}
