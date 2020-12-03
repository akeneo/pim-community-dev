<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Component\Product\Query;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetViewableCategoryCodes extends GetGrantedCategoryCodes
{
    public function forCategoryCodes(int $userId, array $categoryCodes): array;
}
