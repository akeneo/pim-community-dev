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
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PublishedProductIndexer implements IndexerInterface, BulkIndexerInterface, RemoverInterface, BulkRemoverInterface
{
    /** @var NormalizerInterface */
    private $normalizer;

    /** @var Client */
    private $publishedProductClient;

    /** @var string */
    private $indexType;

    public function __construct(
        NormalizerInterface $normalizer,
        Client $publishedProductClient,
        string $indexType
    ) {
        $this->normalizer = $normalizer;
        $this->publishedProductClient = $publishedProductClient;
        $this->indexType = $indexType;
    }

    /**
     * {@inheritdoc}
     */
    public function index($object, array $options = []) : void
    {
        $normalizedObject = $this->normalizer->normalize($object, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX);
        $this->validateObjectNormalization($normalizedObject);
        $this->publishedProductClient->index($this->indexType, $normalizedObject['id'], $normalizedObject);
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

        $this->publishedProductClient->bulkIndexes($this->indexType, $normalizedPublishedProducts, 'id', $indexRefresh);
    }

    /**
     * Removes the product from both the product index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function remove($objectId, array $options = []) : void
    {
        $this->publishedProductClient->delete($this->indexType, $objectId);
    }

    /**
     * Removes the products from both the product index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function removeAll(array $objects, array $options = []) : void
    {
        $this->publishedProductClient->bulkDelete($this->indexType, $objects);
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
