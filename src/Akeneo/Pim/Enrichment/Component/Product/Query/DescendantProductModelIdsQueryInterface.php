<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DescendantProductModelIdsQueryInterface
{
    /**
     * Fetches product model ids from a parent product model id
     *
     * @param int $parentProductModelId
     *
     * @return int[]
     */
    public function fetchFromParentProductModelId(int $parentProductModelId): array;
}
