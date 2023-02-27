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
    public function __construct(private Client $esClient)
    {
    }

    /**
     * {@inheritdoc}
     * @param mixed $queryBuilder
     * @param array<array-key, mixed> $options
     */
    public function createCursor($queryBuilder, array $options = []): IdentifierResultCursor
    {
        /** @var array{_source: array<string>, sort?: array<string>, size: int} $queryBuilder */

        if (!isset($queryBuilder['sort']) || \count($queryBuilder['sort']) === 0) {
            throw new \InvalidArgumentException('PQB requires sort to be defined');
        }

        $options = $this->resolveOptions($options);

        $queryBuilder['_source'] = \array_merge($queryBuilder['_source'], ['document_type', 'id']);
        $queryBuilder['size'] = $options['limit'];
        if (0 !== \count($options['search_after'])) {
            $queryBuilder['search_after'] = $options['search_after'];
        }

        $response = $this->esClient->search($queryBuilder);
        $totalCount = (int) $response['hits']['total']['value'];

        $identifiers = [];
        foreach ($response['hits']['hits'] as $hit) {
            $identifiers[] = new IdentifierResult($hit['_source']['identifier'], $hit['_source']['document_type'], $hit['_source']['id']);
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
            ],
        );
        $resolver->setDefaults(
            [
                'search_after' => [],
            ],
        );
        $resolver->setAllowedTypes('search_after', 'array');
        $resolver->setAllowedTypes('limit', 'int');

        /** @var array{search_after: array<string>, limit: int} $resolvedOptions */
        $resolvedOptions = $resolver->resolve($options);

        return $resolvedOptions;
    }
}
