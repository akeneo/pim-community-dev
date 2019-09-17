<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResultCursor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchAfterSizeIdentifierResultCursorFactory implements CursorFactoryInterface
{
    /** @var Client */
    private $esClient;

    /**
     * @param Client $esClient
     * @param string $indexType
     */
    public function __construct(Client $esClient)
    {
        $this->esClient = $esClient;
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($esQuery, array $options = [])
    {
        $options = $this->resolveOptions($options);
        $sort = ['_id' => 'asc'];

        $esQuery['_source'] = array_merge($esQuery['_source'], ['document_type']);
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
            // TODO: add TODO with TIP card to merge index as we will use only one index instead of 3, removing coalesce
            $documentType = $hit['_source']['document_type'] ?? ProductInterface::class;
            $identifiers[] = new IdentifierResult($hit['_source']['identifier'], $documentType);
        }

        return new IdentifierResultCursor($identifiers, $totalCount);
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
