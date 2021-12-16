<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\Query;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductModelsWithRemovedAttributeInterface;

final class CountProductModelsWithRemovedAttributes implements CountProductModelsWithRemovedAttributeInterface
{
    public function __construct(private CountProductModelsWithRemovedAttributeInterface $decoratedQuery)
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
