<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductCompletenesses
{
    /**
     * @param int $productId
     *
     * @return ProductCompletenessCollection
     */
    public function fromProductId(int $productId): ProductCompletenessCollection;
}
