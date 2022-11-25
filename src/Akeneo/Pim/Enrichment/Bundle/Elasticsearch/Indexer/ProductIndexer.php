<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\ChunkProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Ramsey\Uuid\UuidInterface;

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
    // 4MB of raw values can product a query of 25MB (x7) for the indexation, due to the encoding of UTF-8 characters
    // also, it's possible to consume two times the memory, if you manipulate copy the values into another variable,
    // hence a conservative ratio of x14
    private const MEMORY_RATIO = 14;

    public function __construct(
        private Client $productAndProductModelClient,
        private GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection,
        private ChunkProductUuids $chunkProductUuids
    ) {
    }

    /**
     * Indexes a list of products in the product_and_product_model index from their uuids.
     *
     * If the index_refresh is provided, it uses the refresh strategy defined.
     * Otherwise the waitFor strategy is by default.
     *
     * {@inheritdoc}
     */
    public function indexFromProductUuids(array $productUuids, array $options = []): void
    {
        if (empty($productUuids)) {
            return;
        }

        $indexRefresh = $options['index_refresh'] ?? Refresh::disable();

        $maxMemoryPerChunk = (int) floor($this->memoryLimitAsBytes()/ self::MEMORY_RATIO);
        $chunks =$this->chunkProductUuids->byRawValuesSize($productUuids, $maxMemoryPerChunk);
        foreach ($chunks as $productUuidsChunk) {
            $elasticsearchProductProjections = $this->getElasticsearchProductProjection->fromProductUuids(
                $productUuidsChunk
            );

            $this->productAndProductModelClient->bulkIndexes($elasticsearchProductProjections, 'id', $indexRefresh);
        }
    }

    /**
     * @param UuidInterface[] $productUuids
     */
    public function removeFromProductUuids(array $productUuids): void
    {
        if ([] === $productUuids) {
            return;
        }

        $this->productAndProductModelClient->bulkDelete(array_map(
            fn (UuidInterface $productUuid): string => self::PRODUCT_IDENTIFIER_PREFIX . $productUuid->toString(),
            $productUuids
        ));
    }

    private function memoryLimitAsBytes(): int
    {
        $limit = ini_get('memory_limit');
        if (false === $limit) {
            return 512 * 1024 * 1024;
        }

        if ($limit === '-1') {
            return 512 * 1024 * 1024 * 1024;
        }

        $lastCharacter = substr($limit, -1);
        if ($lastCharacter === 'K') {
            return  (int) substr($limit, 0, -1) * 1024;
        }
        if ($lastCharacter === 'M') {
            return  (int) substr($limit, 0, -1) * 1024 * 1024;
        }
        if ($lastCharacter === 'G') {
            return  (int) substr($limit, 0, -1) * 1024 * 1024 * 1024;
        }

        return (int) $limit;
    }
}
