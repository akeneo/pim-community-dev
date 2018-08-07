<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByCompletenessIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createAttribute([
            'code'        => 'name',
            'type'        => 'pim_catalog_textarea',
            'group'       => 'attributeGroupA',
            'localizable' => true,
            'scopable'    => false,
        ]);

        $this->createFamily([
            'code'        => 'localized',
            'attributes'  => ['sku', 'name'],
            'attribute_requirements' => [
                'tablet' => ['sku', 'name']
            ]

        ]);

        $this->createProduct('french', [
            'family' => 'localized',
            'values'     => [
                'name' => [
                    ['data' => 'French name', 'locale' => 'fr_FR', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('english', [
            'family' => 'localized',
            'values'     => [
                'name' => [
                    ['data' => 'English name', 'locale' => 'en_US', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('complete', [
            'family' => 'localized',
            'values'     => [
                'name' => [
                    ['data' => 'French complete', 'locale' => 'fr_FR', 'scope' => null],
                    ['data' => 'English complete', 'locale' => 'en_US', 'scope' => null],
                ]
            ]
        ]);

        $this->createProduct('empty', ['family' => 'localized']);
    }

    public function testProductExportWithCompleteProductsOnAtLeastOneLocale()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;name-en_US
french;;1;localized;;
english;;1;localized;;"English name"
complete;;1;localized;;"English complete"

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'completeness',
                        'operator' => '=',
                        'value'    => '100',
                        'context'  => [
                            'locales' => ['fr_FR', 'en_US']
                        ]
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

    public function testProductExportWithCompleteProductsOnAllLocales()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;name-en_US
complete;;1;localized;;"English complete"

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'completeness',
                        'operator' => 'GREATER OR EQUALS THAN ON ALL LOCALES',
                        'value'    => '100',
                        'context'  => [
                            'locales' => ['fr_FR', 'en_US']
                        ]
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

    public function testProductExportWithIncompleteProductsOnAllLocales()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;name-en_US
empty;;1;localized;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'completeness',
                        'operator' => 'LOWER THAN ON ALL LOCALES',
                        'value'    => '100',
                        'context'  => [
                            'locales' => ['fr_FR', 'en_US']
                        ]
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

    public function testProductExportWithoutFilterOnCompleteness()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;name-en_US
french;;1;localized;;
english;;1;localized;;"English name"
complete;;1;localized;;"English complete"
empty;;1;localized;;

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

        $this->assertProductExport($expectedCsv, $config);
    }
}
