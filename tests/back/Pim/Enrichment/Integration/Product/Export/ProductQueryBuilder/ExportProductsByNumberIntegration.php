<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByNumberIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [
            new SetNumberValue('a_number_integer', null, null, 100),
        ]);

        $this->createProduct('product_2', [
            new SetNumberValue('a_number_integer', null, null, 110),
        ]);

        $this->createProduct('product_3');
    }

    public function testProductExportByFilteringOnANumber(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_number_integer
{$product1->getUuid()->toString()};product_1;;1;;;100

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_number_integer',
                        'operator' => '=',
                        'value'    => 100
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
