<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsBySimpleSelectReferenceDataIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_ref_data_simple_select']
        ]);

        $this->createProduct('product_option_baby_blue', [
            'family' => 'a_family',
            'values'     => [
                'a_ref_data_simple_select' => [
                    ['data' => 'baby-blue', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_option_champagne', [
            'family' => 'a_family',
            'values'     => [
                'a_ref_data_simple_select' => [
                    ['data' => 'champagne', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_without_option', [
            'family' => 'a_family',
            'values'     => [
                'a_ref_data_simple_select' => [
                    ['data' => null, 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_without_option_attribute',['family' => 'a_family']);

    }

    public function testProductExportByFilteringOnOneOption()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_ref_data_simple_select
product_option_baby_blue;;1;a_family;;baby-blue

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_ref_data_simple_select',
                        'operator' => 'IN',
                        'value'    => ['baby-blue'],
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

    public function testProductExportByFilteringOnTwoOptions()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_ref_data_simple_select
product_option_baby_blue;;1;a_family;;baby-blue
product_option_champagne;;1;a_family;;champagne

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_ref_data_simple_select',
                        'operator' => 'IN',
                        'value'    => ['baby-blue', 'champagne'],
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

    public function testProductExportByFilteringWithEmpty()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_ref_data_simple_select
product_without_option;;1;a_family;;
product_without_option_attribute;;1;a_family;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_ref_data_simple_select',
                        'operator' => 'EMPTY',
                        'value'    => [],
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

    public function testProductExportByFilteringWithAnEmptyList()
    {
        $expectedCsv = <<<CSV

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_ref_data_simple_select',
                        'operator' => 'IN',
                        'value'    => [],
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
