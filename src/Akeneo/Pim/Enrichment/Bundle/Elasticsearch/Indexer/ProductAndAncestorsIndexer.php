<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetAncestorProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * Indexer responsible for the indexation of product entities and parent product model entities of the products.
 * It indexes parent product models because the ES documents of these parent entities contain information about the children products.
 *
 * This indexer SHOULD be used when you update a product, as you have to update the parent document in Elasticsearch.
 *
 * The Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer exists only to be used in this class (SRP).
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndAncestorsIndexer
{
    public function __construct(
        private ProductIndexerInterface $productIndexer,
        private ProductModelIndexerInterface $productModelIndexer,
        private GetAncestorProductModelCodes $getAncestorProductModelCodes
    ) {
    }

    public function indexFromProductUuids(array $uuids, array $options = []): void
    {
        $ancestorProductModelCodes = $this->getAncestorProductModelCodes->fromProductUuids($uuids);
        if (!empty($ancestorProductModelCodes)) {
            $this->productModelIndexer->indexFromProductModelCodes($ancestorProductModelCodes, $options);
        }
        $this->productIndexer->indexFromProductUuids($uuids, $options);
    }

    /**
     * Deletes products from the search engine and reindexes their ancestors. As the products do not exist anymore,
     * we need to provide the ancestors' codes in order to reindex them.
     *
     * @param UuidInterface[] $productUuids
     * @param string[] $ancestorProductModelCodes
     */
    public function removeFromProductUuidsAndReindexAncestors(array $productUuids, array $ancestorProductModelCodes): void
    {
        $this->productIndexer->removeFromProductUuids($productUuids);
        $this->productModelIndexer->indexFromProductModelCodes($ancestorProductModelCodes);
    }
}
