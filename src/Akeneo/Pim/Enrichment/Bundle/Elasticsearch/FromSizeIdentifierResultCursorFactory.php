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
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FromSizeIdentifierResultCursorFactory implements CursorFactoryInterface
{
    /** @var Client */
    private $esClient;

    /**
     * @param Client $esClient
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

        $esQuery['track_total_hits'] = true;
        $esQuery['_source'] = array_merge($esQuery['_source'], ['document_type']);
        $esQuery['sort'] = isset($esQuery['sort']) ? array_merge($esQuery['sort'], $sort) : $sort;
        $esQuery['size'] = $options['limit'];
        $esQuery['from'] = $options['from'];

        $response = $this->esClient->search($esQuery);
        $totalCount = (int) $response['hits']['total']['value'];

        $identifiers = [];
        foreach ($response['hits']['hits'] as $hit) {
            // TODO: remove default type when TIP-1151 and TIP 1150 are done, as the document type will always exist
            $documentType = $hit['_source']['document_type'] ?? ProductInterface::class;
            $identifiers[] = new IdentifierResult($hit['_source']['identifier'], $documentType);
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
