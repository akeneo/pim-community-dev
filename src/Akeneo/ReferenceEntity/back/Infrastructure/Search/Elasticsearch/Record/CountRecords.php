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
        $matches = $this->recordClient->search($elasticSearchQuery);

        return $matches['hits']['total']['value'];
    }

    private function getElasticSearchQuery(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        return [
            '_source' => '_id',
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'reference_entity_code' => (string) $referenceEntityIdentifier,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'track_total_hits' => true,
        ];
    }
}
