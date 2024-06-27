<?php

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductModelsWithRemovedAttributeInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

final class CountProductModelsWithRemovedAttribute implements CountProductModelsWithRemovedAttributeInterface
{
    private readonly SearchQueryBuilder $searchQueryBuilder;

    public function __construct(
        private readonly Client $elasticsearchClient
    ) {
        $this->searchQueryBuilder = new SearchQueryBuilder();
    }

    public function count(array $attributesCodes, bool $includeProductModelsWithoutValue = true): int
    {
        $this->searchQueryBuilder->addFilter([
            'term' => [
                'document_type' => ProductModelInterface::class,
            ],
        ]);
        $this->searchQueryBuilder->addFilter([
            'terms' => [
                'attributes_for_this_level' => $attributesCodes,
            ],
        ]);

        if (!$includeProductModelsWithoutValue) {
            foreach ($attributesCodes as $attributeCode) {
                $this->searchQueryBuilder->addShould([
                    'exists' => ['field' => sprintf('values.%s-*', $attributeCode)],
                ]);
            }
        }

        $body = $this->searchQueryBuilder->getQuery();
        unset($body['_source']);
        unset($body['sort']);

        $result = $this->elasticsearchClient->count($body);

        return (int)$result['count'];
    }

    public function getQueryBuilder(): SearchQueryBuilder
    {
        return $this->searchQueryBuilder;
    }
}
