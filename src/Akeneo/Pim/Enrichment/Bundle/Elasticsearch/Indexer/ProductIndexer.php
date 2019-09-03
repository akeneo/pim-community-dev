<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Indexer responsible for the indexing of products entities. Each product should be normalized in the right format
 * prior to be indexed in the both product and product and product model indexes elasticsearch.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIndexer implements ProductIndexerInterface
{
    private const PRODUCT_IDENTIFIER_PREFIX = 'product_';

    /**
     * Index type is not used anymore in elasticsearch 6, but is still needed by Client
     */
    const INDEX_TYPE = 'pim_catalog_product';

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
     * Indexes a product in the product and product model index from their identifiers.
     *
     * {@inheritdoc}
     */
    public function indexFromProductIdentifier(string $productIdentifier, array $options = []): void
    {
        $this->indexFromProductIdentifiers([$productIdentifier], $options);
    }

    /**
     * Indexes a list of products in the product and product model index from their identifiers.
     *
     * If the index_refresh is provided, it uses the refresh strategy defined.
     * Otherwise the waitFor strategy is by default.
     *
     * {@inheritdoc}
     */
    public function indexFromProductIdentifiers(array $productIdentifiers, array $options = []): void
    {
        $indexRefresh = $options['index_refresh'] ?? Refresh::disable();

        $normalizedProducts = [];
        foreach ($productIdentifiers as $productIdentifier) {
            $object = $this->productRepository->findOneByIdentifier($productIdentifier);
            if (!$object instanceof ProductInterface) {
                continue;
            }

            $normalizedProduct = $this->normalizer->normalize(
                $object,
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
            );
            $this->validateObjectNormalization($normalizedProduct);
            $normalizedProducts[] = $normalizedProduct;
        }

        if (!empty($normalizedProducts)) {
            $this->productAndProductModelClient->bulkIndexes(
                self::INDEX_TYPE,
                $normalizedProducts,
                'id',
                $indexRefresh
            );
        }
    }

    /**
     * Removes the product from the product index and the product model index.
     *
     * {@inheritdoc}
     */
    public function removeFromProductId(int $productId, array $options = []): void
    {
        $this->productAndProductModelClient->delete(self::INDEX_TYPE, self::PRODUCT_IDENTIFIER_PREFIX . $productId);
    }

    /**
     * Removes the products from the product index and the product model index.
     *
     * {@inheritdoc}
     */
    public function removeFromProductIds(array $productIds, array $options = []): void
    {
        $this->productAndProductModelClient->bulkDelete(self::INDEX_TYPE, array_map(
            function ($productId) {
                return self::PRODUCT_IDENTIFIER_PREFIX . (string) $productId;
            },
            $productIds
        ));
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
