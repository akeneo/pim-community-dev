<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetAncestorAndDescendantProductModelCodes;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetDescendantVariantProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;

/**
 * Indexer responsible for the indexation of product model entities, children products, and ancestors.
 * It indexes children products as the ES product documents contain a lot of data inherited from the product model (values, categories, etc).
 * It indexes product model ancestor as the ES product model documents contain some data about the children (is complete/is not complete for example).
 *
 * This indexer SHOULD be used when you update a product model, as you have to update the children documents in Elasticsearch.
 *
 * The Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer exists only to be used in this class (SRP).
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelDescendantsAndAncestorsIndexer
{
    public function __construct(
        private ProductIndexerInterface $productIndexer,
        private ProductModelIndexerInterface $productModelIndexer,
        private GetDescendantVariantProductUuids $getDescendantVariantProductUuids,
        private GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
    }

    /**
     * Indexes the given product models with children (subtrees made of product variants and product models).
     *
     * @param string[] $productModelCodes
     */
    public function indexFromProductModelCodes(array $productModelCodes): void
    {
        if (empty($productModelCodes)) {
            return;
        }

        $ancestorAndDescendantsProductModelCodes = $this
            ->getAncestorAndDescendantProductModelCodes
            ->fromProductModelCodes($productModelCodes)
        ;
        $this->productModelIndexer->indexFromProductModelCodes(
            \array_unique(\array_merge($productModelCodes, $ancestorAndDescendantsProductModelCodes))
        );

        $variantProductUuids = $this->getDescendantVariantProductUuids->fromProductModelCodes(
            $productModelCodes
        );

        if (!empty($variantProductUuids)) {
            $this->productIndexer->indexFromProductUuids($variantProductUuids);
        }
    }

    /**
     * Remove product model and descendants from index, and re-index ancestor.
     *
     * @param array $productModelIds
     */
    public function removeFromProductModelIds(array $productModelIds): void
    {
        if (empty($productModelIds)) {
            return;
        }

        $this->productModelIndexer->removeFromProductModelIds($productModelIds);

        $rootProductModelCodes = $this
            ->getAncestorAndDescendantProductModelCodes
            ->getOnlyAncestorsFromProductModelIds($productModelIds);
        if (!empty($rootProductModelCodes)) {
            $this->productModelIndexer->indexFromProductModelCodes($rootProductModelCodes);
        }
    }
}
