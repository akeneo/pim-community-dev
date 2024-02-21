<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
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
            new SetBooleanValue('a_boolean_scopable_localizable', 'tablet', 'en_US', true)
        ]);

        $this->createProduct('product_with_boolean_true', [
            new SetBooleanValue('a_yes_no', null, null, true)
        ]);

        $this->createProduct('product_with_boolean_false', [
            new SetBooleanValue('a_yes_no', null, null, false)
        ]);

        $this->createproduct('product_without_boolean', [
            new SetNumberValue('a_number_float', null, null, '20.09')
        ]);
    }

    public function testProductExportWithBooleanFilterEqualsTrue(): void
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_with_boolean_true');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_yes_no
{$product->getUuid()->toString()};product_with_boolean_true;;1;;;1

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
            'with_uuid' => true,
        ];

        $this->assertProductExport($expectedCsv, $config);
    }

    public function testProductExportWithBooleanFilterEqualsFalse(): void
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_with_boolean_false');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_yes_no
{$product->getUuid()->toString()};product_with_boolean_false;;1;;;0

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
            'with_uuid' => true,
        ];

        $this->assertProductExport($expectedCsv, $config);
    }

    public function testProductExportWithLocalisableAndScopableBooleanFilter(): void
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_with_localisable_scopable_boolean');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_boolean_scopable_localizable-en_US-tablet
{$product->getUuid()->toString()};product_with_localisable_scopable_boolean;;1;;;1

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
            'with_uuid' => true,
        ];

        $this->assertProductExport($expectedCsv, $config);
    }
}
