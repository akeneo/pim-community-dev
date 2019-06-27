<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByTextIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_text']
        ]);

        $this->createProduct('product_1', [
            'family' => 'a_family',
            'values'     => [
                'a_text' => [
                    ['data' => 'Awesome', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_2', [
            'family' => 'a_family',
            'values'     => [
                'a_text' => [
                    ['data' => 'Awesome product', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_3', [
            'family' => 'a_family',
            'values'     => [
                'a_text' => [
                    ['data' => 'This is nice', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_4', ['family' => 'a_family']);
    }

    public function testProductExportByFilteringWithEqualsOperatorOnText()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_text
product_1;;1;a_family;;Awesome

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_text',
                        'operator' => '=',
                        'value'    => 'Awesome'
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

    public function testProductExportByFilteringWithContainsOperatorOnText()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_text
product_1;;1;a_family;;Awesome
product_2;;1;a_family;;"Awesome product"

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_text',
                        'operator' => 'CONTAINS',
                        'value'    => 'Awesome'
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

    public function testProductExportByFilteringWithStartWithOperatorOnText()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_text
product_1;;1;a_family;;Awesome
product_2;;1;a_family;;"Awesome product"

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_text',
                        'operator' => 'STARTS WITH',
                        'value'    => 'Aw'
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

    public function testProductExportByFilteringWithIsEmptyOperatorOnText()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_text
product_4;;1;a_family;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_text',
                        'operator' => 'EMPTY',
                        'value'    => 'Aw'
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
