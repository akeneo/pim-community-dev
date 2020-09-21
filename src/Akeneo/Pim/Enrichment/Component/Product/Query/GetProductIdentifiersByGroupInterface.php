<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

/**
 * Query to fetch product identifiers linked to a group
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductIdentifiersByGroupInterface
{
    /**
     * @return string[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function fetchByGroupId(int $groupId): array;
}
