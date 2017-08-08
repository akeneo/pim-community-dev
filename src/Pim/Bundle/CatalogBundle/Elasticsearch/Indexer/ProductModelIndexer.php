<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Indexer;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModelFormat\ProductModelNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product model indexer, define custom logic and options for product model indexing in the search engine.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelIndexer implements IndexerInterface, BulkIndexerInterface, RemoverInterface, BulkRemoverInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var Client */
    protected $productAndProductModelClient;

    /** @var string */
    protected $indexType;

    /**
     * @param NormalizerInterface $normalizer
     * @param Client              $productAndProductModelClient
     * @param string              $indexType
     */
    public function __construct(
        NormalizerInterface $normalizer,
        Client $productAndProductModelClient,
        string $indexType
    ) {
        $this->normalizer = $normalizer;
        $this->productAndProductModelClient = $productAndProductModelClient;
        $this->indexType = $indexType;
    }

    /**
     * Indexes a product in both the product index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function index($object, array $options = []) : void
    {
        $normalizedObject = $this->normalizer->normalize(
            $object,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        );
        $this->validateObjectNormalization($normalizedObject);
        $this->productAndProductModelClient->index($this->indexType, $normalizedObject['id'], $normalizedObject);
    }

    /**
     * Indexes a product in both the product index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function indexAll(array $objects, array $options = []) : void
    {
        if (empty($objects)) {
            return;
        }

        $normalizedObjects = [];

        foreach ($objects as $object) {
            $normalizedObject = $this->normalizer->normalize(
                $object,
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
            );
            $this->validateObjectNormalization($normalizedObject);
            $normalizedObjects[] = $normalizedObject;
        }

        $this->productAndProductModelClient->bulkIndexes(
            $this->indexType,
            $normalizedObjects,
            'id',
            Refresh::waitFor()
        );
    }

    /**
     * Removes the product from both the product index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function remove($objectId, array $options = []) : void
    {
        $this->productAndProductModelClient->delete($this->indexType, $objectId);
    }

    /**
     * Removes the products from both the product index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function removeAll(array $objects, array $options = []) : void
    {
        $this->productAndProductModelClient->bulkDelete($this->indexType, $objects);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateObjectNormalization(array $normalization) : void
    {
        if (!isset($normalization['id'])) {
            throw new \InvalidArgumentException(
                'Only product models with an "id" property can be indexed in the search engine.'
            );
        }
    }
}

