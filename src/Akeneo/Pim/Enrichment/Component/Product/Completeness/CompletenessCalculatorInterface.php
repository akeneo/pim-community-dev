<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;

/**
 * Calculates the completenesses for a provided product.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface CompletenessCalculatorInterface
{
    /**
     * Generates an array of completenesses.
     *
     * @param ProductInterface $product
     *
     * @return CompletenessInterface[]
     *
     * @deprecated
     */
    public function calculate(ProductInterface $product): array;

    /**
     * @param string[] $productIdentifiers
     *
     * @return ProductCompletenessCollection[]
     */
    public function fromProductIdentifiers($productIdentifiers): array;
}
