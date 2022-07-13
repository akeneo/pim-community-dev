<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\CountRecordsInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class CountRecords implements CountRecordsInterface
{
    public function __construct(
        private Client $recordClient
    ) {
    }

    public function forReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): int
    {
        $elasticSearchQuery = $this->getElasticSearchQuery($referenceEntityIdentifier);
        $matches = $this->recordClient->count($elasticSearchQuery);

        return $matches['count'];
    }

    public function all(): int
    {
        $matches = $this->recordClient->count([]);

        return $matches['count'];
    }

    private function getElasticSearchQuery(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        return [
            'query' => [
                'term' => [
                    'reference_entity_code' => (string) $referenceEntityIdentifier,
                ],
            ],
        ];
    }
}
