<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Indexer;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Indexer responsible for the indexing of products entities. Each product should be normalized in the right format
 * prior to be indexed in the both product and product and product model indexes elasticsearch.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIndexer implements IndexerInterface, BulkIndexerInterface, RemoverInterface, BulkRemoverInterface
{
    private const PRODUCT_IDENTIFIER_PREFIX = 'product_';
    /** @var NormalizerInterface */
    private $normalizer;

    /** @var Client */
    private $productClient;

    /** @var Client */
    private $productAndProductModelClient;

    /** @var string */
    private $indexType;

    /**
     * @param NormalizerInterface $normalizer
     * @param Client              $productClient
     * @param Client              $productAndProductModelClient
     * @param string              $indexType
     */
    public function __construct(
        NormalizerInterface $normalizer,
        Client $productClient,
        Client $productAndProductModelClient,
        string $indexType
    ) {
        $this->normalizer = $normalizer;
        $this->productClient = $productClient;
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
        $normalizedObject = $this->normalizer->normalize($object, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX);
        $this->validateObjectNormalization($normalizedObject);
        $this->productClient->index($this->indexType, $normalizedObject['id'], $normalizedObject);

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

        $normalizedProducts = [];
        $normalizedProductModels = [];
        foreach ($objects as $object) {
            $normalizedProduct = $this->normalizer->normalize(
                $object,
                ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX
            );
            $this->validateObjectNormalization($normalizedProduct);
            $normalizedProducts[] = $normalizedProduct;

            $normalizedProductModel = $this->normalizer->normalize(
                $object,
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
            );
            $this->validateObjectNormalization($normalizedProductModel);
            $normalizedProductModels[] = $normalizedProductModel;
        }

        $this->productClient->bulkIndexes($this->indexType, $normalizedProducts, 'id', $indexRefresh);
        $this->productAndProductModelClient->bulkIndexes(
            $this->indexType,
            $normalizedProductModels,
            'id',
            $indexRefresh
        );
    }

    /**
     * Removes the product from both the product index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function remove($objectId, array $options = []) : void
    {
        $this->productClient->delete($this->indexType, $objectId);
        $this->productAndProductModelClient->delete(
            $this->indexType,
            self::PRODUCT_IDENTIFIER_PREFIX . (string) $objectId
        );
    }

    /**
     * Removes the products from both the product index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function removeAll(array $objects, array $options = []) : void
    {
        $this->productClient->bulkDelete($this->indexType, $objects);
        $objectIds = [];
        foreach ($objects as $objectId) {
            $objectIds[]  = self::PRODUCT_IDENTIFIER_PREFIX . (string) $objectId;
        }
        $this->productAndProductModelClient->bulkDelete($this->indexType, $objectIds);
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
