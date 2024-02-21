<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindProductUuidsInGroup
{
    /**
     * @return string[] uuids (as strings) of the products belonging to the group
     */
    public function forGroupId(int $groupId): array;
}
