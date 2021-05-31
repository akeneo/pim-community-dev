<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersForQueryInterface;
use Akeneo\AssetManager\Domain\Query\Asset\IdentifiersForQueryResult;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FindIdentifiersForQuery implements FindIdentifiersForQueryInterface
{
    private Client $assetClient;
    private AssetQueryBuilderInterface $assetQueryBuilder;

    public function __construct(
        Client $assetClient,
        AssetQueryBuilderInterface $assetQueryBuilder
    ) {
        $this->assetClient = $assetClient;
        $this->assetQueryBuilder = $assetQueryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetQuery $assetQuery): IdentifiersForQueryResult
    {
        $elasticSearchQuery = $this->assetQueryBuilder->buildFromQuery($assetQuery, '_id');
        $matches = $this->assetClient->search($elasticSearchQuery);
        $identifiers = $this->getIdentifiers($matches);
        $lastSortValue = $this->getLastSortValue($matches);

        return new IdentifiersForQueryResult($identifiers, $matches['hits']['total']['value'], $lastSortValue);
    }

    /**
     * @param array $matches
     *
     * @return string[]
     */
    private function getIdentifiers(array $matches): array
    {
        return array_map(fn (array $hit) => $hit['_id'], $matches['hits']['hits']);
    }

    private function getLastSortValue(array $matches): ?array
    {
        $lastHit = end($matches['hits']['hits']);

        return $lastHit['sort'] ?? null;
    }
}
