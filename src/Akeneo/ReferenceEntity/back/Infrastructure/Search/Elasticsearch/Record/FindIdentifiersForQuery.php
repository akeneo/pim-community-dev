<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersForQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\IdentifiersForQueryResult;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class FindIdentifiersForQuery implements FindIdentifiersForQueryInterface
{
    public function __construct(
        private Client $recordClient,
        private RecordQueryBuilderInterface $recordQueryBuilder
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function find(RecordQuery $recordQuery): IdentifiersForQueryResult
    {
        $elasticSearchQuery = $this->recordQueryBuilder->buildFromQuery($recordQuery, '_id');
        $matches = $this->recordClient->search($elasticSearchQuery);
        $identifiers = $this->getIdentifiers($matches);
        $lastSortValue = $this->getLastSortValue($matches);

        return new IdentifiersForQueryResult($identifiers, $matches['hits']['total']['value'], $lastSortValue);
    }

    /**
     * @return string[]
     */
    private function getIdentifiers(array $matches): array
    {
        return array_map(static fn (array $hit) => $hit['_id'], $matches['hits']['hits']);
    }

    private function getLastSortValue(array $matches): ?string
    {
        $lastHit = end($matches['hits']['hits']);

        return is_array($lastHit) ? (string)$lastHit['sort'][0] : null;
    }
}
