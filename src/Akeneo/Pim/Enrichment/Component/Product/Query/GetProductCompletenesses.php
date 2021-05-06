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
    public function fromProductId(int $productId): ProductCompletenessCollection;

    /**
     * @param array $productIds
     * @param string|null $channel Filtered by given channel
     * @param array $locales Filtered by given locales
     *
     * @return array{int: ProductCompletenessCollection} Array indexed by product id
     */
    public function fromProductIds(array $productIds, ?string $channel = null, array $locales = []): array;
}
