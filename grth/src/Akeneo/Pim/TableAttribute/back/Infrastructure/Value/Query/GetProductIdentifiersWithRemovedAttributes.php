<?php

declare(strict_types=1);

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\Query;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductIdentifiersWithRemovedAttributeInterface;

final class GetProductIdentifiersWithRemovedAttributes implements GetProductIdentifiersWithRemovedAttributeInterface
{
    public function __construct(private GetProductIdentifiersWithRemovedAttributeInterface $decoratedQuery)
    {
    }

    public function getQueryBuilder(): SearchQueryBuilder
    {
        return $this->decoratedQuery->getQueryBuilder();
    }

    public function nextBatch(array $attributesCodes, int $batchSize): iterable
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

        return $this->decoratedQuery->nextBatch($attributesCodes, $batchSize);
    }
}
