<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\IndexableProduct;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetIndexableProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;

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

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var Client */
    private $productAndProductModelClient;

    /** @var \Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetIndexableProductInterface */
    private $getIndexableProduct;

    public function __construct(Client $productAndProductModelClient, GetIndexableProductInterface $getIndexableProduct)
    {
        $this->productAndProductModelClient = $productAndProductModelClient;
        $this->getIndexableProduct = $getIndexableProduct;
    }

    /**
     * Indexes a product in the product and product model index from its identifier.
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
        if (empty($productIdentifiers)) {
            return;
        }

        $indexRefresh = $options['index_refresh'] ?? Refresh::disable();

        $normalizedProducts = array_map(
            function (IndexableProduct $indexableProduct) {
                $normalizedProduct = $indexableProduct->toArray();
                $this->validateObjectNormalization($normalizedProduct);

                return $normalizedProduct;
            },
            $this->getIndexableProduct->fromProductIdentifiers($productIdentifiers)
        );

        if (!empty($normalizedProducts)) {
            $this->productAndProductModelClient->bulkIndexes(
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
        $this->productAndProductModelClient->delete(self::PRODUCT_IDENTIFIER_PREFIX . $productId);
    }

    /**
     * Removes the products from the product index and the product model index.
     *
     * {@inheritdoc}
     */
    public function removeFromProductIds(array $productIds, array $options = []): void
    {
        $this->productAndProductModelClient->bulkDelete(array_map(
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
