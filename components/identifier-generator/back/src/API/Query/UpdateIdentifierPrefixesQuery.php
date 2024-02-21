<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\API\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UpdateIdentifierPrefixesQuery
{
    /**
     * @param ProductInterface[] $products
     */
    public function updateFromProducts(array $products): bool;
}
