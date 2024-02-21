<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByPriceCollectionsIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [
            new SetPriceCollectionValue('a_price', null, null, [
                new PriceValue(10, 'EUR'),
                new PriceValue(20, 'USD'),
            ]),
        ]);

        $this->createProduct('product_2', [
            new SetPriceCollectionValue('a_price', null, null, [
                new PriceValue(20, 'EUR'),
                new PriceValue(10, 'USD')
            ]),
        ]);

        $this->createProduct('product_3');
    }

    public function testProductExportByFilteringOnProductInferiorToAPrice(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_price-EUR;a_price-USD
{$product1->getUuid()->toString()};product_2;;1;;;20.00;10.00

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
            'with_uuid' => true,
        ];

        $this->assertProductExport($expectedCsv, $config);
    }
}
