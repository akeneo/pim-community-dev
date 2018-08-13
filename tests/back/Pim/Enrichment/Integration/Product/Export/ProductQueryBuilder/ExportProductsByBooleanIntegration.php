<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByBooleanIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createAttribute([
            'code'        => 'a_boolean_scopable_localizable',
            'type'        => 'pim_catalog_boolean',
            'group'       => 'attributeGroupA',
            'localizable' => true,
            'scopable'    => true,
        ]);

        $this->createProduct('product_with_localisable_scopable_boolean', [
            'values'     => [
                'a_boolean_scopable_localizable' => [['data' => true, 'locale' => 'en_US', 'scope' => 'tablet']],
            ]
        ]);

        $this->createProduct('product_with_boolean_true', [
            'values'     => [
                'a_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]],
            ]
        ]);

        $this->createProduct('product_with_boolean_false', [
            'values'     => [
                'a_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]],
            ]
        ]);

        $this->createproduct('product_without_boolean', [
            'values'     => [
                'a_number_float' => [['data' => '20.09', 'locale' => null, 'scope' => null]],
            ]
        ]);
    }

    public function testProductExportWithBooleanFilterEqualsTrue()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_yes_no
product_with_boolean_true;;1;;;1

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_yes_no',
                        'operator' => '=',
                        'value'    => true,
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

    public function testProductExportWithBooleanFilterEqualsFalse()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_yes_no
product_with_boolean_false;;1;;;0

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_yes_no',
                        'operator' => '=',
                        'value'    => false,
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

    public function testProductExportWithLocalisableAndScopableBooleanFilter()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_boolean_scopable_localizable-en_US-tablet
product_with_localisable_scopable_boolean;;1;;;1

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_boolean_scopable_localizable',
                        'operator' => '=',
                        'value'    => true,
                        'context'  => ['locale' => 'en_US', 'scope' => 'tablet']
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
