<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DescendantProductIdsQueryInterface
{
    /**
     * Fetches product ids from many product model ids
     *
     * @param int[] $productModelIds
     *
     * @return int[]
     */
    public function fetchFromProductModelIds(array $productModelIds): array;
}
