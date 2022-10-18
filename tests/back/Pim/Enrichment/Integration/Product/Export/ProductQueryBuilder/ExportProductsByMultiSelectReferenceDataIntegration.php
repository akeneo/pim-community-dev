<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceEntityValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByMultiSelectReferenceDataIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_ref_data_multi_select']
        ]);

        $this->createProduct('product_airguard', [
            new SetFamily('a_family'),
            new SetMultiReferenceEntityValue('a_ref_data_multi_select', null, null, ['airguard'])
        ]);

        $this->createProduct('product_braid', [
            new SetFamily('a_family'),
            new SetMultiReferenceEntityValue('a_ref_data_multi_select', null, null, ['braid'])
        ]);

        $this->createProduct('product_airguard_braid', [
            new SetFamily('a_family'),
            new SetMultiReferenceEntityValue('a_ref_data_multi_select', null, null, ['airguard', 'braid'])
        ]);

        $this->createProduct('product_without_option', [
            new SetFamily('a_family'),
            new ClearValue('a_ref_data_multi_select', null, null)
        ]);

        $this->createProduct('product_without_option_attribute', [new SetFamily('a_family')]);
    }

    public function testProductExportByFilteringOnOneOption(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_airguard');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_airguard_braid');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_ref_data_multi_select
{$product1->getUuid()->toString()};product_airguard;;1;a_family;;airguard
{$product2->getUuid()->toString()};product_airguard_braid;;1;a_family;;airguard,braid

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_ref_data_multi_select',
                        'operator' => 'IN',
                        'value'    => ['airguard'],
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

    public function testProductExportByFilteringOnTwoOptions(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_airguard');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_airguard_braid');
        $product3 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_braid');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_ref_data_multi_select
{$product1->getUuid()->toString()};product_airguard;;1;a_family;;airguard
{$product2->getUuid()->toString()};product_airguard_braid;;1;a_family;;airguard,braid
{$product3->getUuid()->toString()};product_braid;;1;a_family;;braid

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_ref_data_multi_select',
                        'operator' => 'IN',
                        'value'    => ['airguard', 'braid'],
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

    public function testProductExportByFilteringWithEmpty(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_without_option');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_without_option_attribute');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_ref_data_multi_select
{$product1->getUuid()->toString()};product_without_option;;1;a_family;;
{$product2->getUuid()->toString()};product_without_option_attribute;;1;a_family;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_ref_data_multi_select',
                        'operator' => 'EMPTY',
                        'value'    => [],
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

    public function testProductExportByFilteringWithAnEmptyList(): void
    {
        $expectedCsv = '';

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_ref_data_multi_select',
                        'operator' => 'IN',
                        'value'    => [],
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
