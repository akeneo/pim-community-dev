<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByNumberIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [
            'values'     => [
                'a_number_integer' => [
                    ['data' => 100, 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_2', [
            'values' => [
                'a_number_integer' => [
                    ['data' => 110, 'locale' => null, 'scope' => null]
                ]
            ],
        ]);

        $this->createProduct('product_3');
    }

    public function testProductExportByFilteringOnANumber()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_number_integer
product_1;;1;;;100

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_number_integer',
                        'operator' => '=',
                        'value'    => 100
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
