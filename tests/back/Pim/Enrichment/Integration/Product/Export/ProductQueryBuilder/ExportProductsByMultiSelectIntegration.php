<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByMultiSelectIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_multi_select']
        ]);

        $this->createProduct('product_option_A', [
            new SetFamily('a_family'),
            new SetMultiSelectValue('a_multi_select', null, null, ['optionA'])
        ]);

        $this->createProduct('product_option_B', [
            new SetFamily('a_family'),
            new SetMultiSelectValue('a_multi_select', null, null, ['optionB'])
        ]);

        $this->createProduct('product_option_A_B', [
            new SetFamily('a_family'),
            new SetMultiSelectValue('a_multi_select', null, null, ['optionA', 'optionB'])
        ]);

        $this->createProduct('product_without_option', [
            new SetFamily('a_family'),
            new SetMultiSelectValue('a_multi_select', null, null, [])
        ]);

        $this->createProduct('product_without_option_attribute', [new SetFamily('a_family')]);

    }

    public function testProductExportByFilteringOnOneOption()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_multi_select
product_option_A;;1;a_family;;optionA
product_option_A_B;;1;a_family;;optionA,optionB

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_multi_select',
                        'operator' => 'IN',
                        'value'    => ['optionA'],
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
sku;categories;enabled;family;groups;a_multi_select
product_option_A;;1;a_family;;optionA
product_option_B;;1;a_family;;optionB
product_option_A_B;;1;a_family;;optionA,optionB

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_multi_select',
                        'operator' => 'IN',
                        'value'    => ['optionA', 'optionB'],
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
sku;categories;enabled;family;groups;a_multi_select
product_without_option;;1;a_family;;
product_without_option_attribute;;1;a_family;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_multi_select',
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
                        'field'    => 'a_multi_select',
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
