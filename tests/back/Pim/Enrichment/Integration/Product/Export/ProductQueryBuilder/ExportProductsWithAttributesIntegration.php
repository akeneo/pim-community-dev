<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsWithAttributesIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createFamily([
            'code'        => 'my_family',
            'attributes'  => ['a_text', 'a_text_area'],
            'attribute_requirements' => [
                'tablet' => ['a_text', 'a_text_area']
            ]

        ]);

        $this->createProduct('product_1', [
            'family'     => 'my_family',
            'values'     => [
                'a_text' => [
                    ['data' => 'Awesome', 'locale' => null, 'scope' => null]
                ],
                'a_text_area' => [
                    ['data' => 'Amazing', 'locale' => null, 'scope' => null]
                ],
            ]
        ]);

        $this->createProduct('product_2', [
            'family'     => 'my_family',
            'values'     => [
                'a_text' => [
                    ['data' => 'Awesome product', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_4');
    }

    public function testProductExportBySelectingOnlyOneAttribute()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_text_area
product_1;;1;my_family;;Amazing
product_2;;1;my_family;;
product_4;;1;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                    'attributes'=> ['a_text_area'],
                ],
            ],
        ];

        $this->assertProductExport($expectedCsv, $config);
    }

    public function testProductExportWithAttributesInTheSameOrderAsTheFilter()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_text_area;a_text
product_1;;1;my_family;;Amazing;Awesome
product_2;;1;my_family;;;"Awesome product"
product_4;;1;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                    'attributes'=> ['a_text_area', 'a_text'],
                ],
            ],
        ];

        $this->assertProductExport($expectedCsv, $config);

        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_text;a_text_area
product_1;;1;my_family;;Awesome;Amazing
product_2;;1;my_family;;"Awesome product";
product_4;;1;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                    'attributes'=> ['a_text', 'a_text_area'],
                ],
            ],
        ];

        $csv = $this->jobLauncher->launchSubProcessExport('csv_product_export', null, $config);

        $this->assertSame($expectedCsv, $csv);
    }

    /**
     * @jira https://akeneo.atlassian.net/browse/PIM-5994
     */
    public function testProductExportByExportingTheAttributesOnlyOnce()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_text_area;a_text
product_1;;1;my_family;;Amazing;Awesome
product_2;;1;my_family;;;"Awesome product"

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'family',
                        'operator' => 'IN',
                        'value'    => ['my_family']
                    ],
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US'],
                    'attributes'=> ['a_text_area', 'a_text'],
                ],
            ],
        ];

        $this->assertProductExport($expectedCsv, $config);
    }
}
