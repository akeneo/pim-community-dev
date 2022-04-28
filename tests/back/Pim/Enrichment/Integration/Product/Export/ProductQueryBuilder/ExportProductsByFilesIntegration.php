<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByFilesIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [
            // TODO: use SetImageValue when ready
            new SetTextValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.png')))
        ]);

        $this->createProduct('product_2', [
            // TODO: use SetImageValue when ready
            new SetTextValue('an_image', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.png')))
        ]);
    }

    public function testProductExportWithFilterEqualsOnFileValue()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;an_image
product_1;;1;;;files/product_1/an_image/akeneo.png

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
        ];

        $this->assertProductExport($expectedCsv, $config);
    }

    public function testProductExportWithFilterStartWithOnFileValue()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;an_image
product_1;;1;;;files/product_1/an_image/akeneo.png
product_2;;1;;;files/product_2/an_image/akeneo.jpg

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
        ];

        $this->assertProductExport($expectedCsv, $config);
    }
}
