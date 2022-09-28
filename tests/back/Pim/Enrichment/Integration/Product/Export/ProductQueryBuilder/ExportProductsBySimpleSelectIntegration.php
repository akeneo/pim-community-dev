<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsBySimpleSelectIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_simple_select']
        ]);

        $this->createProduct('product_option_A', [
            new SetFamily('a_family'),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionA')
        ]);

        $this->createProduct('product_option_B', [
            new SetFamily('a_family'),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionB')
        ]);

        $this->createProduct('product_without_option', [
            new SetFamily('a_family'),
            new ClearValue('a_simple_select', null, null)
        ]);

        $this->createProduct('product_without_option_attribute', [new SetFamily('a_family')]);

    }

    public function testProductExportByFilteringOnOneOption(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_option_A');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_simple_select
{$product1->getUuid()->toString()};product_option_A;;1;a_family;;optionA

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_simple_select',
                        'operator' => 'IN',
                        'value'    => ['optionA'],
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
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_option_A');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_option_B');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_simple_select
{$product1->getUuid()->toString()};product_option_A;;1;a_family;;optionA
{$product2->getUuid()->toString()};product_option_B;;1;a_family;;optionB

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_simple_select',
                        'operator' => 'IN',
                        'value'    => ['optionA', 'optionB'],
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
uuid;sku;categories;enabled;family;groups;a_simple_select
{$product1->getUuid()->toString()};product_without_option;;1;a_family;;
{$product2->getUuid()->toString()};product_without_option_attribute;;1;a_family;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'a_simple_select',
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
                        'field'    => 'a_simple_select',
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
