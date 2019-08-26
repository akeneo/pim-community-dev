<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Indexer responsible for the indexing of products entities. Each product should be normalized in the right format
 * prior to be indexed in the both product and product and product model indexes elasticsearch.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIndexer implements IndexerInterface, BulkIndexerInterface, RemoverInterface, BulkRemoverInterface, ProductIndexerInterface
{
    private const PRODUCT_IDENTIFIER_PREFIX = 'product_';

    /**
     * Index type is not used anymore in ES6 but is still needed by Client
     */
    private const INDEX_TYPE = '';

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var Client */
    private $productAndProductModelClient;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param NormalizerInterface        $normalizer
     * @param Client                     $productAndProductModelClient
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        NormalizerInterface $normalizer,
        Client $productAndProductModelClient,
        ProductRepositoryInterface $productRepository
    ) {
        $this->normalizer = $normalizer;
        $this->productAndProductModelClient = $productAndProductModelClient;
        $this->productRepository = $productRepository;
    }

    /**
     * Indexes a product in both the product index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function indexFromProductIdentifier(string $productIdentifier, array $options = []): void
    {
        $object = $this->productRepository->findOneByIdentifier($productIdentifier);
        if (!$object instanceof ProductInterface) {
            return;
        }

        $normalizedObject = $this->normalizer->normalize(
            $object,
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        );
        $this->validateObjectNormalization($normalizedObject);
        $this->productAndProductModelClient->index(self::INDEX_TYPE, $normalizedObject['id'], $normalizedObject);
    }

    /**
     * Indexes a list of products in both the product index and the product and product model index.
     *
     * If the index_refresh is provided, it uses the refresh strategy defined.
     * Otherwise the waitFor strategy is by default.
     *
     * {@inheritdoc}
     */
    public function indexFromProductIdentifiers(array $productIdentifiers, array $options = []): void
    {
        $indexRefresh = $options['index_refresh'] ?? Refresh::disable();

        $normalizedProductModels = [];
        foreach ($productIdentifiers as $productIdentifier) {
            $object = $this->productRepository->findOneByIdentifier($productIdentifier);
            if (!$object instanceof ProductInterface) {
                continue;
            }

            $normalizedProductModel = $this->normalizer->normalize(
                $object,
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
            );
            $this->validateObjectNormalization($normalizedProductModel);
            $normalizedProductModels[] = $normalizedProductModel;
        }

        if (empty($normalizedProductModels)) {
            return;
        }

        $this->productAndProductModelClient->bulkIndexes(
            self::INDEX_TYPE,
            $normalizedProductModels,
            'id',
            $indexRefresh
        );
    }

    /**
     * Removes the products from both the product index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function removeFromProductIdentifier(string $productIdentifier, array $options = []): void
    {
        $this->productAndProductModelClient->delete(
            self::INDEX_TYPE,
            self::PRODUCT_IDENTIFIER_PREFIX . $productIdentifier
        );
    }

    /**
     * Removes the products from both the product index and the product and product model index.
     *
     * {@inheritdoc}
     */
    public function removeManyFromProductIdentifiers(array $productIdentifiers, array $options = []): void
    {
        if (empty($productIdentifiers)) {
            return;
        }

        $this->productAndProductModelClient->bulkDelete(
            self::INDEX_TYPE,
            array_map(
                function (string $productIdentifiers) {
                    return self::PRODUCT_IDENTIFIER_PREFIX . $productIdentifiers;
                },
                $productIdentifiers
            )
        );
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
        $this->productAndProductModelClient->index(self::INDEX_TYPE, $normalizedObject['id'], $normalizedObject);
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

        $indexRefresh = $options['index_refresh'] ?? Refresh::disable();

        $normalizedProductModels = [];
        foreach ($objects as $object) {
            $normalizedProductModel = $this->normalizer->normalize(
                $object,
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
            );
            $this->validateObjectNormalization($normalizedProductModel);
            $normalizedProductModels[] = $normalizedProductModel;
        }

        $this->productAndProductModelClient->bulkIndexes(
            self::INDEX_TYPE,
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
        $this->productAndProductModelClient->delete(
            self::INDEX_TYPE,
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
        $objectIds = [];
        foreach ($objects as $objectId) {
            $objectIds[]  = self::PRODUCT_IDENTIFIER_PREFIX . (string) $objectId;
        }
        $this->productAndProductModelClient->bulkDelete(self::INDEX_TYPE, $objectIds);
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
