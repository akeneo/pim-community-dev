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
    }

    public function testProductExportWithFilterEqualsOnFileValue()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;an_image
product_1;;1;;;files/0bca8f20-be4c-4b9e-ba12-877cc29f6072/an_image/akeneo.jpg

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
        ];

        $this->assertProductExport($expectedCsv, $config);
    }

    public function testProductExportWithFilterStartWithOnFileValue()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;an_image
product_2;;1;;;files/9c6d6019-c7fb-4304-a6be-2a725dc576a7/an_image/ziggy.png

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
        ];

        $this->assertProductExport($expectedCsv, $config);
    }
}
