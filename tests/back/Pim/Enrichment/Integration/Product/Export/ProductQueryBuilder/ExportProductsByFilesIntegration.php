<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByFilesIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProductWithUuid('bafd727c-3562-49a9-aba9-f94f6b9971d3', [
            new SetIdentifierValue('sku', 'product_1'),
            new SetImageValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.png')))
        ]);

        $this->createProductWithUuid('42951cab-b3bc-40f7-a8a9-3f5f366370ff', [
            new SetIdentifierValue('sku', 'product_2'),
            new SetImageValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')))
        ]);

        $this->createProductWithUuid('07284c2e-f9a9-4c04-8007-b1c974d329fe', [
            new SetImageValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')))
        ]);
    }

    public function testProductExportWithFilterEqualsOnFileValue(): void
    {
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;an_image
bafd727c-3562-49a9-aba9-f94f6b9971d3;product_1;;1;;;files/product_1/an_image/akeneo.png

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'an_image',
                        'operator' => '=',
                        'value'    => 'akeneo.png',
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
bafd727c-3562-49a9-aba9-f94f6b9971d3;product_1;;1;;;files/product_1/an_image/akeneo.png
42951cab-b3bc-40f7-a8a9-3f5f366370ff;product_2;;1;;;files/product_2/an_image/akeneo.jpg
07284c2e-f9a9-4c04-8007-b1c974d329fe;;;1;;;files/07284c2e-f9a9-4c04-8007-b1c974d329fe/an_image/akeneo.jpg

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'an_image',
                        'operator' => 'STARTS WITH',
                        'value'    => 'ake',
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
