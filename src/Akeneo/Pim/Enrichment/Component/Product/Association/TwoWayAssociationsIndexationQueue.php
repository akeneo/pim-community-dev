<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

/**
 * Collects the products and product models whose two-way association was mutated
 * as a side effect of saving another entity (the "owner"), so they can be reindexed
 * in Elasticsearch once the whole unit of work has been flushed.
 *
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TwoWayAssociationsIndexationQueue
{
    private array $productUuidsToIndex = [];
    private array $productModelCodesToIndex = [];

    public function queueProduct(ProductInterface $product): void
    {
        $this->productUuidsToIndex[] = $product->getUuid();
    }

    public function queueProductModel(ProductModelInterface $productModel): void
    {
        $this->productModelCodesToIndex[] = $productModel->getCode();
    }

    public function flushProductUuids(): array
    {
        $uuids = $this->productUuidsToIndex;
        $this->productUuidsToIndex = [];

        return $uuids;
    }

    public function flushProductModelCodes(): array
    {
        $codes = $this->productModelCodesToIndex;
        $this->productModelCodesToIndex = [];

        return $codes;
    }
}
