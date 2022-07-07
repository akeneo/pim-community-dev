<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByStatusIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [new SetEnabled(true)]);

        $this->createProduct('product_2', [new SetEnabled(false)]);

        $this->createProduct('product_3');
    }

    public function testProductExportByFilteringOnEnableProducts()
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_3');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups
%s;product_1;;1;;
%s;product_3;;1;;

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

        $this->assertProductExport(\sprintf($expectedCsv, $product1->getUuid()->toString(), $product2->getUuid()->toString()), $config);
    }

    public function testProductExportByFilteringOnDisableProducts()
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups
%s;product_2;;0;;

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

        $this->assertProductExport(\sprintf($expectedCsv, $product1->getUuid()->toString()), $config);
    }

    public function testProductExportWithoutFilterOnStatus()
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2');
        $product3 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_3');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups
%s;product_1;;1;;
%s;product_2;;0;;
%s;product_3;;1;;

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

        $this->assertProductExport(\sprintf(
            $expectedCsv,
            $product1->getUuid()->toString(),
            $product2->getUuid()->toString(),
            $product3->getUuid()->toString(),
        ), $config);
    }
}
