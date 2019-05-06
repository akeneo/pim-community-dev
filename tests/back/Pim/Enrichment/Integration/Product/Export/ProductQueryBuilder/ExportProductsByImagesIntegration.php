<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByImagesIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_1', [
            'values'     => [
                'an_image' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_2', [
            'values'     => [
                'an_image' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('ziggy.png')), 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
    }

    public function testProductExportWithFilterEqualsOnFileValue()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;an_image
product_1;;1;;;files/product_1/an_image/akeneo.jpg

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
product_2;;1;;;files/product_2/an_image/ziggy.png

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
