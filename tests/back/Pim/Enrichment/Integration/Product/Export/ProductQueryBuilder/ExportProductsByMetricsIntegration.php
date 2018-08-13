<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByMetricsIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [
            'values'     => [
                'a_metric_without_decimal_negative' => [
                    ['data' => ['amount' => -10, 'unit' => 'CELSIUS'], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_2', [
            'values' => [
                'a_metric_without_decimal_negative' => [
                    ['data' => ['amount' => 20, 'unit' => 'CELSIUS'], 'locale' => null, 'scope' => null]
                ]
            ],
        ]);

        $this->createProduct('product_3');
    }

    public function testProductExportByFilteringOnMetric()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_metric_without_decimal_negative;a_metric_without_decimal_negative-unit
product_1;;1;;;-10;CELSIUS

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_metric_without_decimal_negative',
                        'operator' => '<',
                        'value'    => ['amount' => 15, 'unit' => 'CELSIUS']
                    ]
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
        ];

        $this->assertProductExport($expectedCsv, $config);
    }
}
