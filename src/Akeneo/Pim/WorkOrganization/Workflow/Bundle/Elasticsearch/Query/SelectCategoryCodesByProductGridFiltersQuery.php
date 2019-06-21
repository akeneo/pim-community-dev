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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectCategoryCodesByProductGridFiltersQueryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

class SelectCategoryCodesByProductGridFiltersQuery implements SelectCategoryCodesByProductGridFiltersQueryInterface
{
    private $pqbFactory;

    private $esClient;

    public function __construct(ProductQueryBuilderFactoryInterface $pqbFactory, Client $esClient)
    {
        $this->pqbFactory = $pqbFactory;
        $this->esClient = $esClient;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $filters): array
    {
        $query = $this->buildQuery($filters);

        $results = $this->esClient->search('pim_catalog_product', $query);

        $categories = array_map(function ($result) {
            return $result['key'];
        }, $results['aggregations']['categories']['buckets'] ?? []);

        return $categories;
    }

    private function buildQuery(array $filters): array
    {
        $pqb = $this->pqbFactory->create(['filters' => $filters]);
        $query = $pqb->getQueryBuilder()->getQuery(['categories']);

        $query['_source'] = [];
        $query['size'] = 0;
        $query['aggregations'] = [
            'categories' => [
                'terms' => [
                    'field' => 'categories'
                ]
            ]
        ];

        return $query;
    }
}
