<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\TemporaryEnrichmentBridge;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ElasticsearchResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResultCursor;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @internal
 * @deprecated
 *
 * @todo CXP-1186 REMOVE
 */
class SearchAfterSizeUuidResultCursorFactory implements CursorFactoryInterface
{
    /** @var Client */
    private $esClient;

    public function __construct(Client $esClient)
    {
        $this->esClient = $esClient;
    }

    /**
     * {@inheritdoc}
     * @param mixed $queryBuilder
     * @param array<array-key, mixed> $options
     */
    public function createCursor($queryBuilder, array $options = [])
    {
        /** @var array{_source: array<string>, sort?: array<string>, size: int} $queryBuilder */

        $options = $this->resolveOptions($options);
        $sort = ['_id' => 'asc'];

        $queryBuilder['_source'] = \array_merge($queryBuilder['_source'], ['document_type']);
        $queryBuilder['sort'] = isset($queryBuilder['sort']) ? \array_merge($queryBuilder['sort'], $sort) : $sort;
        $queryBuilder['size'] = $options['limit'];
        if (0 !== \count($options['search_after'])) {
            $queryBuilder['search_after'] = $options['search_after'];
        }

        $response = $this->esClient->search($queryBuilder);
        $totalCount = (int) $response['hits']['total']['value'];

        $identifiers = [];
        foreach ($response['hits']['hits'] as $hit) {
            $identifiers[] = new IdentifierResult($hit['_source']['identifier'], $hit['_source']['document_type']);
        }

        return new IdentifierResultCursor($identifiers, $totalCount, new ElasticsearchResult($response));
    }

    /**
     * @param array<array-key, mixed> $options
     * @return array{search_after: array<string>, limit: int}
     */
    protected function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(
            [
                'search_after',
                'limit',
            ]
        );
        $resolver->setDefaults(
            [
                'search_after' => [],
            ]
        );
        $resolver->setAllowedTypes('search_after', 'array');
        $resolver->setAllowedTypes('limit', 'int');

        return $resolver->resolve($options);
    }
}
