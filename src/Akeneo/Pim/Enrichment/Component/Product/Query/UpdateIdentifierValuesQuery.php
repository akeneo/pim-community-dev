<?php

declare(strict_types=1);


namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UpdateIdentifierValuesQuery
{
    /**
     * @param ProductInterface[] $products
     */
    public function forProducts(array $products): void;
}
