<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Storage\GetProductAndProductModelIdsWithValuesIgnoringLocaleAndScope;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ESGetProductAndProductModelIdsWithValuesIgnoringLocaleAndScope implements GetProductAndProductModelIdsWithValuesIgnoringLocaleAndScope
{
    /** @var Client */
    private $client;

    /** @var int */
    private $batchSize;

    public function __construct(Client $client, int $batchSize)
    {
        $this->client = $client;
        $this->batchSize = $batchSize;
    }

    public function setBatchSize(int $batchSize): void
    {
        $this->batchSize = $batchSize;
    }

    public function forAttributeAndValues(Attribute $attribute, array $values): iterable
    {
        $attributePath = sprintf('values.%s-%s.*', $attribute->getCode(), $attribute->getBackendType());

        $baseQuery = [
            '_source' => ['identifier'],
            'size' => $this->batchSize,
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'query_string' => [
                                'query' => implode(' OR ', $values),
                                'fields' => [$attributePath],
                            ],
                        ],
                        [
                            'term' => [
                                'attributes_for_this_level' => $attribute->getCode(),
                            ],
                        ],
                    ],
                ],
            ],
            'sort' => ['_id' => 'asc'],
        ];

        $searchAfter = null;
        $count = 0;
        while (true) {
            $count++;
            if ($count > 10) break;

            $query = $baseQuery;
            if ($searchAfter !== null) {
                $query['search_after'] = $searchAfter;
            }

            $response = $this->client->search($query);

            $hits = $response['hits']['hits'] ?? [];
            if (0 === count($hits)) {
                break;
            }

            $identifiers = array_map(function (array $hit) {
                return $hit['_source']['identifier'];
            }, $hits);
            yield $identifiers;

            $lastResult = end($hits);
            $searchAfter = $lastResult['sort'];
        }

    }
}
