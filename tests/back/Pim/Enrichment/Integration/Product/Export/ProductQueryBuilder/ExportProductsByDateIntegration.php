<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByDateIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [
            'values'     => [
                'a_date' => [
                    ['data' => '2025-12-31', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_2', [
            'values'     => [
                'a_date' => [
                    ['data' => '2016-06-15', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
    }

    public function testProductExportWithFilterSuperiorToADate()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_date
product_1;;1;;;2025-12-31

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_date',
                        'operator' => '>',
                        'value'    => '2016-08-13',
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

    public function testProductExportWithFilterInferiorToADate()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_date
product_2;;1;;;2016-06-15

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_date',
                        'operator' => '<',
                        'value'    => '2016-08-13',
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
}
