<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByDateIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [
            new SetDateValue('a_date', null, null, new \DateTime('2025-12-31'))
        ]);

        $this->createProduct('product_2', [
            new SetDateValue('a_date', null, null, new \DateTime('2016-06-15'))
        ]);
    }

    public function testProductExportWithFilterSuperiorToADate(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_date
{$product1->getUuid()->toString()};product_1;;1;;;2025-12-31

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_date',
                        'operator' => '>',
                        'value'    => '2016-08-13',
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

    public function testProductExportWithFilterInferiorToADate(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_date
{$product1->getUuid()->toString()};product_2;;1;;;2016-06-15

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_date',
                        'operator' => '<',
                        'value'    => '2016-08-13',
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
