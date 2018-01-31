<?php

namespace Pim\Bundle\ConnectorBundle\tests\integration\Export\ProductQueryBuilder;

use Pim\Bundle\ConnectorBundle\tests\integration\Export\AbstractExportTestCase;

class ExportProductsByTextIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [
            'values'     => [
                'a_text' => [
                    ['data' => 'Awesome', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_2', [
            'values'     => [
                'a_text' => [
                    ['data' => 'Awesome product', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_3', [
            'values'     => [
                'a_text' => [
                    ['data' => 'This is nice', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_4');
    }

    public function testProductExportByFilteringWithEqualsOperatorOnText()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_text
product_1;;1;;;Awesome

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
product_1;;1;;;Awesome
product_2;;1;;;"Awesome product"

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
product_1;;1;;;Awesome
product_2;;1;;;"Awesome product"

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
sku;categories;enabled;family;groups
product_4;;1;;

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
