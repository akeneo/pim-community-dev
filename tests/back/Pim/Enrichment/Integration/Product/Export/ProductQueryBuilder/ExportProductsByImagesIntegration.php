<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByImagesIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProductWithUuid('0bca8f20-be4c-4b9e-ba12-877cc29f6072', [
            new SetIdentifierValue('sku', 'product_1'),
            new SetImageValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')))
        ]);

        $this->createProductWithUuid('9c6d6019-c7fb-4304-a6be-2a725dc576a7', [
            new SetIdentifierValue('sku', 'product_2'),
            new SetImageValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('ziggy.png')))
        ]);

        $this->createProductWithUuid('7f754d14-2b31-4373-90d0-5d377ed93b57', [
            new SetImageValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('ziggy.png')))
        ]);
    }

    public function testProductExportWithFilterEqualsOnFileValue(): void
    {
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;an_image
0bca8f20-be4c-4b9e-ba12-877cc29f6072;product_1;;1;;;files/product_1/an_image/akeneo.jpg

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
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;an_image
9c6d6019-c7fb-4304-a6be-2a725dc576a7;product_2;;1;;;files/product_2/an_image/ziggy.png
7f754d14-2b31-4373-90d0-5d377ed93b57;;;1;;;files/7f754d14-2b31-4373-90d0-5d377ed93b57/an_image/ziggy.png

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
