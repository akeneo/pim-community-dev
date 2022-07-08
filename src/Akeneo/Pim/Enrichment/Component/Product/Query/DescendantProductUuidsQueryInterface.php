<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Ramsey\Uuid\UuidInterface;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DescendantProductUuidsQueryInterface
{
    /**
     * Fetches product ids from many product model ids
     *
     * @param int[] $productModelIds
     *
     * @return UuidInterface[]
     */
    public function fetchFromProductModelIds(array $productModelIds): array;
}
