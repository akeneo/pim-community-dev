<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductGrid;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierResultCursorFactory implements CursorFactoryInterface
{
    /** @var Client */
    private $esClient;

    /** @var string */
    private $indexType;

    /**
     * @param Client $esClient
     * @param string $indexType
     */
    public function __construct(Client $esClient, string $indexType)
    {
        $this->esClient = $esClient;
        $this->indexType = $indexType;
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($esQuery, array $options = [])
    {
        $options = $this->resolveOptions($options);
        $sort = ['_uid' => 'asc'];

        $esQuery['_source'] = array_merge($esQuery['_source'], ['document_type']);
        $esQuery['sort'] = isset($esQuery['sort']) ? array_merge($esQuery['sort'], $sort) : $sort;
        $esQuery['size'] = $options['limit'];
        $esQuery['from'] = $options['from'];

        $response = $this->esClient->search($this->indexType, $esQuery);
        $totalCount = (int) $response['hits']['total'];

        $identifiers = [];
        foreach ($response['hits']['hits'] as $hit) {
            $identifiers[] = new IdentifierResult($hit['_source']['identifier'], $hit['_source']['document_type']);
        }

        return new IdentifierResultCursor($identifiers, $totalCount);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(
            [
                'limit',
                'from',
            ]
        );
        $resolver->setDefaults(
            [
                'from' => 0
            ]
        );
        $resolver->setAllowedTypes('limit', 'int');
        $resolver->setAllowedTypes('from', 'int');

        $options = $resolver->resolve($options);

        return $options;
    }
}
