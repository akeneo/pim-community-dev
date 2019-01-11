<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByPriceCollectionsIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [
            'values'     => [
                'a_price' => [[
                    'data' => [
                        ['amount' => 10, 'currency' => 'EUR'],
                        ['amount' => 20, 'currency' => 'USD']
                    ],
                    'locale' => null,
                    'scope' => null
                ]]
            ]
        ]);

        $this->createProduct('product_2', [
            'values' => [
                'a_price' => [[
                    'data' => [
                        ['amount' => 20, 'currency' => 'EUR'],
                        ['amount' => 10, 'currency' => 'USD']
                    ],
                    'locale' => null,
                    'scope' => null
                ]]
            ],
        ]);

        $this->createProduct('product_3');
    }

    public function testProductExportByFilteringOnProductInferiorToAPrice()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_price-EUR;a_price-USD
product_2;;1;;;20.00;10.00

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_price',
                        'operator' => '>',
                        'value'    => ['amount' => 10, 'currency' => 'EUR']
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
