<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\ProductModel;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetDescendantVariantProductIdentifiers;

/**
 * Orchestrator for below jobs:
 *  - Computes and saves the completenesses for the variant products of the given product models.
 *  - Indexes these variant products
 *  - Indexes the product models impacted by the new completeness (= ancestors and descendants of the given product models)
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductCompletenessAndIndexProductAndAncestors
{
    /** @var ComputeAndPersistProductCompletenesses */
    private $computeAndPersistProductCompletenesses;

    /** @var ProductModelDescendantsAndAncestorsIndexer */
    private $productModelDescendantsAndAncestorsIndexer;

    /** @var GetDescendantVariantProductIdentifiers */
    private $getDescendantVariantProductIdentifiers;

    public function __construct(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers
    ) {
        $this->computeAndPersistProductCompletenesses = $computeAndPersistProductCompletenesses;
        $this->productModelDescendantsAndAncestorsIndexer = $productModelDescendantsAndAncestorsIndexer;
        $this->getDescendantVariantProductIdentifiers = $getDescendantVariantProductIdentifiers;
    }

    /**
     * We can make some optimizations here: if no variant products, we can only index product models
     * with the product model descendants. Ancestors needs to be indexed only if completeness changes.
     * We can also:
     *  - avoid to calculate the completeness if no required attribute has changed: so no indexation of the ancestors also
     *  - avoid to calculate the products if no values, association, categories, etc has changed
     *
     * @param array $productModelCodes
     */
    public function fromProductModelCodes(array $productModelCodes): void
    {
        if (empty($productModelCodes)) {
            return;
        }

        $variantProductIdentifiers = $this->getDescendantVariantProductIdentifiers->fromProductModelCodes(
            $productModelCodes
        );
        if (!empty($variantProductIdentifiers)) {
            $this->computeAndPersistProductCompletenesses->fromProductIdentifiers($variantProductIdentifiers);
        }

        $this->productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes($productModelCodes);
    }
}
