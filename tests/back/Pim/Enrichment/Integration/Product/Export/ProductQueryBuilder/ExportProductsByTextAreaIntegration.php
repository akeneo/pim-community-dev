<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
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
            new SetFamily('a_family'),
            new SetTextareaValue('a_text_area', null, null, 'Awesome')
        ]);

        $this->createProduct('product_2', [
            new SetFamily('a_family'),
            new SetTextareaValue('a_text_area', null, null, 'Awesome product')
        ]);

        $this->createProduct('product_3', [
            new SetFamily('a_family'),
            new SetTextareaValue('a_text_area', null, null, 'This is nice')
        ]);

        $this->createProduct('product_4', [new SetFamily('a_family')]);
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
