<?php

namespace Pim\Bundle\ConnectorBundle\tests\integration\Export\ProductQueryBuilder;

use Pim\Bundle\ConnectorBundle\tests\integration\Export\AbstractExportTestCase;

class ExportProductsByTextAreaIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [
            'values'     => [
                'a_text_area' => [
                    ['data' => 'Awesome', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_2', [
            'values'     => [
                'a_text_area' => [
                    ['data' => 'Awesome product', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_3', [
            'values'     => [
                'a_text_area' => [
                    ['data' => 'This is nice', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_4');
    }

    public function testProductExportByFilteringWithEqualsOperatorOnTextArea()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_text_area
product_1;;1;;;Awesome

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
product_1;;1;;;Awesome
product_2;;1;;;"Awesome product"

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
product_1;;1;;;Awesome
product_2;;1;;;"Awesome product"

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
sku;categories;enabled;family;groups
product_4;;1;;

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
