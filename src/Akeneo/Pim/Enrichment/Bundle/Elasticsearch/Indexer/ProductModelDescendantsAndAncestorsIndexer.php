<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetAncestorAndDescendantProductModelCodes;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetDescendantVariantProductIdentifiers;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;

/**
 * Indexer responsible for the indexing of the product models, product models ancestors
 * and all children (product models and variant products)
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelDescendantsAndAncestorsIndexer
{
    /** @var ProductIndexerInterface */
    private $productIndexer;

    /** @var ProductModelIndexerInterface */
    private $productModelIndexer;

    /** @var GetDescendantVariantProductIdentifiers */
    private $getDescendantVariantProductIdentifiers;

    /** @var GetAncestorAndDescendantProductModelCodes */
    private $getAncestorAndDescendantProductModelCodes;

    public function __construct(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $this->productIndexer = $productIndexer;
        $this->productModelIndexer = $productModelIndexer;
        $this->getDescendantVariantProductIdentifiers = $getDescendantVariantProductIdentifiers;
        $this->getAncestorAndDescendantProductModelCodes = $getAncestorAndDescendantProductModelCodes;
    }

    /**
     * Indexes the given product models with children (subtrees made of product variants and product models).
     *
     * @param string[] $productModelCodes
     * @param array    $options
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
            array_unique(array_merge($productModelCodes, $ancestorAndDescendantsProductModelCodes))
        );

        $variantProductIdentifiers = $this->getDescendantVariantProductIdentifiers->fromProductModelCodes(
            $productModelCodes
        );
        if (!empty($variantProductIdentifiers)) {
            $this->productIndexer->indexFromProductIdentifiers($variantProductIdentifiers);
        }
    }
}
