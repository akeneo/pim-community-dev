<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @todo create interfaces for GetEditableCategoryCodes and GetOwnableCategoryCodes
 * @todo see Akeneo\Pim\Enrichment\Product\Component\Product\Query\GetViewableCategoryCodes
 * @todo delete this interface
 */
interface GetGrantedCategoryCodes
{
    public function forGroupIds(array $groupIds): array;
}
