<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchAfterSizeIdentifierResultCursorFactory implements CursorFactoryInterface
{
    /** @var Client */
    private $esClient;

    public function __construct(Client $esClient)
    {
        $this->esClient = $esClient;
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($esQuery, array $options = []): CursorInterface
    {
        $options = $this->resolveOptions($options);
        $sort = ['id' => 'asc'];

        $esQuery['_source'] = array_merge($esQuery['_source'], ['document_type', 'id']);
        $esQuery['sort'] = isset($esQuery['sort']) ? array_merge($esQuery['sort'], $sort) : $sort;
        $esQuery['size'] = $options['limit'];

        if (null !== $options['search_after_unique_key']) {
            array_push($options['search_after'], $options['search_after_unique_key']);
        }
        if (!empty($options['search_after'])) {
            $esQuery['search_after'] = $options['search_after'];
        }

        $response = $this->esClient->search($esQuery);
        $totalCount = (int) $response['hits']['total']['value'];

        $identifiers = [];
        foreach ($response['hits']['hits'] as $hit) {
            $identifiers[] = new IdentifierResult($hit['_source']['identifier'], $hit['_source']['document_type'], $hit['_source']['id']);
        }

        return new IdentifierResultCursor($identifiers, $totalCount, new ElasticsearchResult($response));
    }

    protected function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(
            [
                'search_after',
                'search_after_unique_key',
                'limit'
            ]
        );
        $resolver->setDefaults(
            [
                'search_after' => [],
                'search_after_unique_key' => null
            ]
        );
        $resolver->setAllowedTypes('search_after', 'array');
        $resolver->setAllowedTypes('search_after_unique_key', ['string', 'null']);
        $resolver->setAllowedTypes('limit', 'int');

        $options = $resolver->resolve($options);

        return $options;
    }
}
