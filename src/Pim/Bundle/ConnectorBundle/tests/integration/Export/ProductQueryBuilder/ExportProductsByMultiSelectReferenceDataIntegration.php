<?php

namespace Pim\Bundle\ConnectorBundle\tests\integration\Export\ProductQueryBuilder;

use Pim\Bundle\ConnectorBundle\tests\integration\Export\AbstractExportTestCase;

class ExportProductsByMultiSelectReferenceDataIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_airguard', [
            'values'     => [
                'a_ref_data_multi_select' => [
                    ['data' => ['airguard'], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_braid', [
            'values'     => [
                'a_ref_data_multi_select' => [
                    ['data' => ['braid'], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_airguard_braid', [
            'values'     => [
                'a_ref_data_multi_select' => [
                    ['data' => ['airguard', 'braid'], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_without_option', [
            'values'     => [
                'a_ref_data_multi_select' => [
                    ['data' => [], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_without_option_attribute');

    }

    public function testProductExportByFilteringOnOneOption()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_ref_data_multi_select
product_airguard;;1;;;airguard
product_airguard_braid;;1;;;airguard,braid

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_ref_data_multi_select',
                        'operator' => 'IN',
                        'value'    => ['airguard'],
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
sku;categories;enabled;family;groups;a_ref_data_multi_select
product_airguard;;1;;;airguard
product_braid;;1;;;braid
product_airguard_braid;;1;;;airguard,braid

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_ref_data_multi_select',
                        'operator' => 'IN',
                        'value'    => ['airguard', 'braid'],
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
sku;categories;enabled;family;groups
product_without_option;;1;;
product_without_option_attribute;;1;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_ref_data_multi_select',
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
                        'field'    => 'a_ref_data_multi_select',
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
