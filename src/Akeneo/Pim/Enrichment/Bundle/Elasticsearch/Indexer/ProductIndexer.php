<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;

/**
 * Indexer responsible for the indexation of product entities. This indexer DOES NOT index product model ancestors that can
 * contain information about the product children, such as number of complete products.
 *
 * This indexer SHOULD NOT be used when you update a product, as you have to update the parent document in Elasticsearch.
 * Please use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer in that case.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIndexer implements ProductIndexerInterface
{
    private const PRODUCT_IDENTIFIER_PREFIX = 'product_';
    private const BATCH_SIZE = 1000;

    /** @var Client */
    private $productAndProductModelClient;

    /** @var GetElasticsearchProductProjectionInterface */
    private $getElasticsearchProductProjection;

    public function __construct(Client $productAndProductModelClient, GetElasticsearchProductProjectionInterface $getElasticsearchProductProjectionQuery)
    {
        $this->productAndProductModelClient = $productAndProductModelClient;
        $this->getElasticsearchProductProjection = $getElasticsearchProductProjectionQuery;
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

        $chunks = array_chunk($productIdentifiers, self::BATCH_SIZE);
        foreach ($chunks as $productIdentifiersChunk) {
            $elasticsearchProductProjections = $this->getElasticsearchProductProjection->fromProductIdentifiers(
                $productIdentifiersChunk
            );
            $normalizedProductProjections = array_map(
                function (ElasticsearchProductProjection $elasticsearchProductProjection) {
                    return $elasticsearchProductProjection->toArray();
                },
                $elasticsearchProductProjections
            );

            $this->productAndProductModelClient->bulkIndexes($normalizedProductProjections, 'id', $indexRefresh);
        }
    }

    /**
     * Removes the product from the product and product model index.
     *
     * {@inheritdoc}
     */
    public function removeFromProductId(int $productId): void
    {
        $this->productAndProductModelClient->delete(self::PRODUCT_IDENTIFIER_PREFIX . $productId);
    }

    /**
     * Removes the products from the product and product model index.
     *
     * {@inheritdoc}
     */
    public function removeFromProductIds(array $productIds): void
    {
        $this->productAndProductModelClient->bulkDelete(array_map(
            function ($productId) {
                return self::PRODUCT_IDENTIFIER_PREFIX . (string) $productId;
            },
            $productIds
        ));
    }
}
