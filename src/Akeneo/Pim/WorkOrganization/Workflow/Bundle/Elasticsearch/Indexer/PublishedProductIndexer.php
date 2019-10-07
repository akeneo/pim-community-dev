<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\PublishedProduct\PublishedProductNormalizer;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PublishedProductIndexer implements IndexerInterface, BulkIndexerInterface, RemoverInterface
{
    /** @var NormalizerInterface */
    private $normalizer;

    /** @var Client */
    private $publishedProductClient;

    public function __construct(
        NormalizerInterface $normalizer,
        Client $publishedProductClient
    ) {
        $this->normalizer = $normalizer;
        $this->publishedProductClient = $publishedProductClient;
    }

    /**
     * {@inheritdoc}
     */
    public function index($object, array $options = []) : void
    {
        $normalizedObject = $this->normalizer->normalize($object, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX);
        $this->validateObjectNormalization($normalizedObject);
        $this->publishedProductClient->index($normalizedObject['id'], $normalizedObject);
    }

    /**
     * If the index_refresh is provided, it uses the refresh strategy defined.
     * Otherwise the waitFor strategy is by default.
     *
     * {@inheritdoc}
     */
    public function indexAll(array $objects, array $options = []) : void
    {
        if (empty($objects)) {
            return;
        }

        $indexRefresh = $options['index_refresh'] ?? Refresh::waitFor();

        $normalizedPublishedProducts = [];
        foreach ($objects as $object) {
            $normalizedProduct = $this->normalizer->normalize(
                $object,
                PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX
            );
            $this->validateObjectNormalization($normalizedProduct);
            $normalizedPublishedProducts[] = $normalizedProduct;
        }

        $this->publishedProductClient->bulkIndexes($normalizedPublishedProducts, 'id', $indexRefresh);
    }

    /**
     * Removes the published product from published product index
     *
     * {@inheritdoc}
     */
    public function remove($publishedProductId, array $options = []) : void
    {
        $this->publishedProductClient->delete($publishedProductId);
    }

    /**
     * Checks the normalized object has the minimum property needed for the indexation to work.
     *
     * @param array $normalization
     */
    protected function validateObjectNormalization(array $normalization) : void
    {
        if (!isset($normalization['id'])) {
            throw new \InvalidArgumentException('Only products with an "id" property can be indexed in the search engine.');
        }
    }
}
