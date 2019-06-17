<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByTextAreaIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_text_area']
        ]);

        $this->createProduct('product_1', [
            'family' => 'a_family',
            'values'     => [
                'a_text_area' => [
                    ['data' => 'Awesome', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_2', [
            'family' => 'a_family',
            'values'     => [
                'a_text_area' => [
                    ['data' => 'Awesome product', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_3', [
            'family' => 'a_family',
            'values'     => [
                'a_text_area' => [
                    ['data' => 'This is nice', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_4', ['family' => 'a_family']);
    }

    public function testProductExportByFilteringWithEqualsOperatorOnTextArea()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_text_area
product_1;;1;a_family;;Awesome

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_text_area',
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

    public function testProductExportByFilteringWithContainsOperatorOnTextArea()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_text_area
product_1;;1;a_family;;Awesome
product_2;;1;a_family;;"Awesome product"

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_text_area',
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

    public function testProductExportByFilteringWithStartWithOperatorOnTextArea()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_text_area
product_1;;1;a_family;;Awesome
product_2;;1;a_family;;"Awesome product"

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_text_area',
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

    public function testProductExportByFilteringWithIsEmptyOperatorOnTextArea()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_text_area
product_4;;1;a_family;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_text_area',
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
