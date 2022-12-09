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

    /**
     * The memory ratio helps to determine the size of the batch in function of the configured PHP memory limit.
     * For example, with a ratio of 60 and a maximum memory of 512 MB,  the cumulated size of the raw values in a batch of products is at most ~8MB (512/60).
     * It seems very conservative, but it is not actually.
     * Indeed, 8MB of raw values in database can generate up to 100MB once passed to json_decode. Additionally, as soon as you manipulates the values
     * in different function in the stack traces, you can reach a big consumption of memory, as PHP could dupicate the whole array of raw values.
     *
     * There would be possibilities to optimize memory consumption it by using reference or wrapping string values in Object to just duplicate the pointer of the objects.
     * Though, it would be less maintainable and an improvement.
s     */
    private const MEMORY_RATIO = 60;

    public function __construct(
        private Client $productAndProductModelClient,
        private GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection,
        private ChunkProductUuids $chunkProductUuids,
        private PhpMemoryLimit $phpMemoryLimit
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

        $maxMemoryPerChunk = (int) floor($this->phpMemoryLimit->asBytesFromPHPConfig()/ self::MEMORY_RATIO);
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


}
