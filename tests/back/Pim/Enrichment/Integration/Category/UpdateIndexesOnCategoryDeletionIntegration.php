<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Category;

use AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration\AbstractPimCatalogTestCase;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateIndexesOnCategoryDeletionIntegration extends AbstractPimCatalogTestCase
{
    /**
     * @test
     */
    public function it_updates_indexes_on_category_deletion()
    {
        $categoryToRemove = $this->get('pim_catalog.repository.category')->findOneByIdentifier('categoryA');
        $this->get('pim_catalog.remover.category')->remove($categoryToRemove);
        $this->esProductClient->refreshIndex();

        $productsInDeletedCategory = $this->getSearchQueryResults([
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                'terms' => [
                                    'categories' => ['categoryA'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertEmpty($productsInDeletedCategory);

        $unclassifiedProducts = $this->getSearchQueryResults([
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'must_not' => [
                                'exists' => ['field' => 'categories'],
                            ],
                        ],
                    ],
                ],
            ],
            'sort' => [
                ['identifier' => ['order' => 'desc'],],
            ],
        ]);
        $this->assertSame(
            ['product-3', 'product-1'],
            $unclassifiedProducts
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function addDocuments()
    {
        $this->indexDocuments([
            [
                'identifier' => 'product-1',
                'categories' => ['categoryA'],
            ],
            [
                'identifier' => 'product-2',
                'categories' => ['categoryA', 'categoryB'],
            ],
            [
                'identifier' => 'product-3',
                'categories' => ['categoryA1'],
            ],
            [
                'identifier' => 'product-4',
                'categories' => ['categoryB'],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
