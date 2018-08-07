<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByStatusIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', ['enabled' => true]);

        $this->createProduct('product_2', ['enabled' => false]);

        $this->createProduct('product_3');
    }

    public function testProductExportByFilteringOnEnableProducts()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups
product_1;;1;;
product_3;;1;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'enabled',
                        'operator' => '=',
                        'value'    => true
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

    public function testProductExportByFilteringOnDisableProducts()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups
product_2;;0;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'enabled',
                        'operator' => '=',
                        'value'    => false
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

    public function testProductExportWithoutFilterOnStatus()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups
product_1;;1;;
product_2;;0;;
product_3;;1;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                ],
            ],
        ];

        $this->assertProductExport($expectedCsv, $config);
    }
}
