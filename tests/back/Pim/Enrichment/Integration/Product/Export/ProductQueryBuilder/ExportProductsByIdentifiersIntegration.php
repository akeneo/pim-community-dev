<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByIdentifiersIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1');
        $this->createProduct('product_2');
    }

    public function testProductExportWithFilterOnOneIdentifier()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups
product_1;;1;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'sku',
                        'operator' => 'IN',
                        'value'    => ['product_1']
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

    public function testProductExportWithFilterOnAListOfIdentifiers()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups
product_1;;1;;
product_2;;1;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'sku',
                        'operator' => 'IN',
                        'value'    => ['product_1', 'product_2']
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
