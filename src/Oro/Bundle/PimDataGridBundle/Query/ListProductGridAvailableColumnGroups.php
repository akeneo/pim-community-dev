<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Query;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ListProductGridAvailableColumnGroups
{
    /**
     * Fetch the list of the available column groups in product grid, with the number of columns in each group.
     *
     * @param string $locale
     * @param int    $userId
     *
     * @return array
     */
    public function fetch(string $locale, int $userId): array;
}
