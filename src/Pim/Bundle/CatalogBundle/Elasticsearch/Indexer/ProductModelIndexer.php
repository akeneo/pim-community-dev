<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Indexer;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductModel;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;
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
    private const PRODUCT_MODEL_IDENTIFIER_PREFIX = 'product_model_';
    /** @var NormalizerInterface */
    private $normalizer;

    /** @var Client */
    private $productModelClient;

    /** @var Client */
    private $productAndProductModelClient;

    /** @var string */
    private $indexType;

    /**
     * @param NormalizerInterface $normalizer
     * @param Client              $productModelClient
     * @param Client              $productAndProductModelClient
     * @param string              $indexType
     */
    public function __construct(
        NormalizerInterface $normalizer,
        Client $productModelClient,
        Client $productAndProductModelClient,
        string $indexType
    ) {
        $this->normalizer = $normalizer;
        $this->productModelClient = $productModelClient;
        $this->productAndProductModelClient = $productAndProductModelClient;
        $this->indexType = $indexType;
    }

    /**
     * Indexes a product in both the product model index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function index($object, array $options = []) : void
    {
        $normalizedObject = $this->normalizer->normalize(
            $object,
            ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX
        );
        $this->validateObjectNormalization($normalizedObject);
        $this->productModelClient->index($this->indexType, $normalizedObject['id'], $normalizedObject);

        $normalizedObject = $this->normalizer->normalize(
            $object,
            ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        );
        $this->validateObjectNormalization($normalizedObject);
        $this->productAndProductModelClient->index($this->indexType, $normalizedObject['id'], $normalizedObject);
    }

    /**
     * Indexes a product in both the product model index and the product and product model index.
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
                ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX
            );
            $this->validateObjectNormalization($normalizedObject);
            $normalizedObjects[] = $normalizedObject;
        }

        $this->productModelClient->bulkIndexes(
            $this->indexType,
            $normalizedObjects,
            'id',
            Refresh::waitFor()
        );

        $normalizedObjects = [];
        foreach ($objects as $object) {
            $normalizedObject = $this->normalizer->normalize(
                $object,
                ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
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
     * Removes the product from both the product model index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function remove($objectId, array $options = []) : void
    {
        $this->productModelClient->delete(
            $this->indexType,
            (string) $objectId
        );

        $this->productAndProductModelClient->delete(
            $this->indexType,
            self::PRODUCT_MODEL_IDENTIFIER_PREFIX . (string) $objectId
        );
    }

    /**
     * Removes the products from both the product model index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function removeAll(array $objects, array $options = []) : void
    {
        $objectIds = [];
        foreach ($objects as $objectId) {
            $objectIds[] = (string) $objectId;
        }
        $this->productModelClient->bulkDelete($this->indexType, $objectIds);

        $objectIds = [];
        foreach ($objects as $objectId) {
            $objectIds[]  = self::PRODUCT_MODEL_IDENTIFIER_PREFIX . (string) $objectId;
        }
        $this->productAndProductModelClient->bulkDelete($this->indexType, $objectIds);
    }

    /**
     * {@inheritdoc}
     */
    private function validateObjectNormalization(array $normalization) : void
    {
        if (!isset($normalization['id'])) {
            throw new \InvalidArgumentException(
                'Only product models with an "id" property can be indexed in the search engine.'
            );
        }
    }
}
