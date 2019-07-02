<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\ElasticSearch\Query;

use Akeneo\Test\Integration\TestCase;

class SelectCategoryCodesByProductGridFiltersQueryIntegration extends TestCase
{
    public function test_it_selects_category_codes_by_product_grid_filters()
    {
        $query = $this->get('pimee_workflow.query.select_category_codes_by_product_grid_filters');

        $productBuilder = $this->get('pim_catalog.builder.product');
        $productA = $productBuilder->createProduct('product_a', 'boots');
        $productB = $productBuilder->createProduct('product_b', 'boots');

        $productUpdater = $this->get('pim_catalog.updater.product');
        $productUpdater->update($productA, ['categories' => ['sandals', 'winter_boots']]);
        $productUpdater->update($productB, ['categories' => ['sandals', '2014_collection']]);

        $this->get('pim_catalog.saver.product')->saveAll([$productA, $productB]);
        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();

        $filters = [
            [
                'field' => 'family',
                'operator' => 'IN',
                'type' => 'field',
                'value' => [
                    'boots'
                ]
            ]
        ];
        $categories = $query->execute($filters);
        $expectedCategories = ['sandals', 'winter_boots', '2014_collection'];

        $this->assertEqualsCanonicalizing($expectedCategories, $categories);
    }

    public function test_it_returns_an_empty_array_if_there_is_no_products_found()
    {
        $query = $this->get('pimee_workflow.query.select_category_codes_by_product_grid_filters');

        $productBuilder = $this->get('pim_catalog.builder.product');
        $productA = $productBuilder->createProduct('product_a', 'boots');
        $productB = $productBuilder->createProduct('product_b', 'boots');

        $productUpdater = $this->get('pim_catalog.updater.product');
        $productUpdater->update($productA, ['categories' => ['sandals', 'winter_boots']]);
        $productUpdater->update($productB, ['categories' => ['sandals', '2014_collection']]);

        $this->get('pim_catalog.saver.product')->saveAll([$productA, $productB]);
        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();

        $filters = [
            [
                'field' => 'family',
                'operator' => 'IN',
                'type' => 'field',
                'value' => [
                    'heels'
                ]
            ]
        ];

        $categories = $query->execute($filters);

        $this->assertSame([], $categories);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('footwear');
    }
}
