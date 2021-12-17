<?php

declare(strict_types=1);

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\Query;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;

final class CountProductsWithRemovedAttributes implements CountProductsWithRemovedAttributeInterface
{
    public function __construct(private CountProductsWithRemovedAttributeInterface $decoratedQuery)
    {
    }

    public function count(array $attributesCodes): int
    {
        foreach ($attributesCodes as $attributeCode) {
            $this->getQueryBuilder()->addShould(
                [
                    'nested' => [
                        'path' => \sprintf('table_values.%s', $attributeCode),
                        'query' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'exists' => [
                                            'field' => \sprintf('table_values.%s.row', $attributeCode),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'ignore_unmapped' => true,
                    ],
                ]
            );
        }

        return $this->decoratedQuery->count($attributesCodes);
    }

    public function getQueryBuilder(): SearchQueryBuilder
    {
        return $this->decoratedQuery->getQueryBuilder();
    }
}
