<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetGroupProductIdentifiers
{
    /**
     * @param int $groupId
     *
     * @return string[] list of product identifiers of the given group.
     */
    public function byGroupId(int $groupId): array;
}
