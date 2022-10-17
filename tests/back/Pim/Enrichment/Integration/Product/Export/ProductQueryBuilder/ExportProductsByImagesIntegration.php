<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByImagesIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [
            new SetImageValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')))
        ]);

        $this->createProduct('product_2', [
            new SetImageValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('ziggy.png')))
        ]);
    }

    public function testProductExportWithFilterEqualsOnFileValue(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;an_image
{$product1->getUuid()->toString()};product_1;;1;;;files/product_1/an_image/akeneo.jpg

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'an_image',
                        'operator' => '=',
                        'value'    => 'akeneo.jpg',
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

    public function testProductExportWithFilterStartWithOnFileValue(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;an_image
{$product1->getUuid()->toString()};product_2;;1;;;files/product_2/an_image/ziggy.png

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'an_image',
                        'operator' => 'STARTS WITH',
                        'value'    => 'zig',
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
