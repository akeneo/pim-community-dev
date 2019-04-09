<?php


namespace Akeneo\Pim\Enrichment\Component\Category\Query;

/**
 * Given a list of category codes, get viewable category codes
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetViewableCategoryCodesInterface
{
    /**
     * @param int $userId
     * @param array $categoryCodes
     * @return array
     */
    public function getViewableCategoryCodes(int $userId, array $categoryCodes): array;
}
