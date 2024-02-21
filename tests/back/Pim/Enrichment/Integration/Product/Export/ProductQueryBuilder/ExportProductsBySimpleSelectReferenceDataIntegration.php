<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsBySimpleSelectReferenceDataIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_ref_data_simple_select']
        ]);

        $this->createProduct('product_option_baby_blue', [
            new SetFamily('a_family'),
            new SetSimpleReferenceEntityValue('a_ref_data_simple_select', null, null, 'baby-blue'),
        ]);

        $this->createProduct('product_option_champagne', [
            new SetFamily('a_family'),
            new SetSimpleReferenceEntityValue('a_ref_data_simple_select', null, null, 'champagne'),
        ]);

        $this->createProduct('product_without_option', [
            new SetFamily('a_family'),
            new ClearValue('a_ref_data_simple_select', null, null),
        ]);

        $this->createProduct('product_without_option_attribute',[new SetFamily('a_family')]);

    }

    public function testProductExportByFilteringOnOneOption(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_option_baby_blue');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_ref_data_simple_select
{$product1->getUuid()->toString()};product_option_baby_blue;;1;a_family;;baby-blue

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_ref_data_simple_select',
                        'operator' => 'IN',
                        'value'    => ['baby-blue'],
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
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_option_baby_blue');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_option_champagne');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_ref_data_simple_select
{$product1->getUuid()->toString()};product_option_baby_blue;;1;a_family;;baby-blue
{$product2->getUuid()->toString()};product_option_champagne;;1;a_family;;champagne

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_ref_data_simple_select',
                        'operator' => 'IN',
                        'value'    => ['baby-blue', 'champagne'],
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
uuid;sku;categories;enabled;family;groups;a_ref_data_simple_select
{$product1->getUuid()->toString()};product_without_option;;1;a_family;;
{$product2->getUuid()->toString()};product_without_option_attribute;;1;a_family;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_ref_data_simple_select',
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
                        'field'    => 'a_ref_data_simple_select',
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
