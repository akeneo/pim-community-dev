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

    public function testProductExportWithFilterOnOneIdentifier(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups
{$product1->getUuid()->toString()};product_1;;1;;

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
            'with_uuid' => true,
        ];

        $this->assertProductExport($expectedCsv, $config);
    }

    public function testProductExportWithFilterOnAListOfIdentifiers(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups
{$product1->getUuid()->toString()};product_1;;1;;
{$product2->getUuid()->toString()};product_2;;1;;

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
            'with_uuid' => true,
        ];

        $this->assertProductExport($expectedCsv, $config);
    }
}
