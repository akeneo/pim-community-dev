<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductsByCategoriesIntegration extends AbstractExportTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createProduct('product_with_categories', [
            new SetCategories(['categoryA', 'categoryA1', 'categoryA2'])
        ]);

        $this->createProduct('product_with_single_category_A', [
            new SetCategories(['categoryA'])
        ]);

        $this->createProduct('product_with_single_category_B', [
            new SetCategories(['categoryB'])
        ]);

        $this->createProduct('product_without_category');
    }

    public function testProductExportWithCategoryFilter(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_with_categories');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_with_single_category_A');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups
{$product1->getUuid()->toString()};product_with_categories;categoryA,categoryA1,categoryA2;1;;
{$product2->getUuid()->toString()};product_with_single_category_A;categoryA;1;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'categories',
                        'operator' => 'IN',
                        'value'    => ['categoryA', 'categoryA1'],
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
