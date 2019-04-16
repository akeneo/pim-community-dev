<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductGrid;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * TODO: add spec
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchAfterSizeIdentifierResultCursorFactory implements CursorFactoryInterface
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
        $sort = ['_uid' => 'asc'];

        $esQuery['_source'] = array_merge($esQuery['_source'], ['document_type']);
        $esQuery['sort'] = isset($esQuery['sort']) ? array_merge($esQuery['sort'], $sort) : $sort;
        $esQuery['size'] = $options['limit'];

        $response = $this->esClient->search($this->indexType, $esQuery);
        $totalCount = (int) $response['hits']['total'];

        $identifiers = [];
        foreach ($response['hits']['hits'] as $hit) {
            // TODO: add TODO with TIP card to merge index as we will use only one index instead of 3, removing coalesce
            $documentType = $hit['_source']['document_type'] ?? ProductInterface::class;
            $identifiers[] = new IdentifierResult($hit['_source']['identifier'], $documentType);
        }

        return new IdentifierResultCursor($identifiers, $totalCount);
    }
}
