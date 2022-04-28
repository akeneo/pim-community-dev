<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceEntityValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByMultiSelectReferenceDataIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_ref_data_multi_select']
        ]);

        $this->createProduct('product_airguard', [
            new SetFamily('a_family'),
            new SetMultiReferenceEntityValue('a_ref_data_multi_select', null, null, ['airguard'])
        ]);

        $this->createProduct('product_braid', [
            new SetFamily('a_family'),
            new SetMultiReferenceEntityValue('a_ref_data_multi_select', null, null, ['braid'])
        ]);

        $this->createProduct('product_airguard_braid', [
            new SetFamily('a_family'),
            new SetMultiReferenceEntityValue('a_ref_data_multi_select', null, null, ['airguard', 'braid'])
        ]);

        $this->createProduct('product_without_option', [
            new SetFamily('a_family'),
            new SetMultiReferenceEntityValue('a_ref_data_multi_select', null, null, [])
        ]);

        $this->createProduct('product_without_option_attribute', [new SetFamily('a_family')]);
    }

    public function testProductExportByFilteringOnOneOption()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_ref_data_multi_select
product_airguard;;1;a_family;;airguard
product_airguard_braid;;1;a_family;;airguard,braid

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
product_airguard;;1;a_family;;airguard
product_braid;;1;a_family;;braid
product_airguard_braid;;1;a_family;;airguard,braid

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
sku;categories;enabled;family;groups;a_ref_data_multi_select
product_without_option;;1;a_family;;
product_without_option_attribute;;1;a_family;;

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
        $expectedCsv = '';

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
