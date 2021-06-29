<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Analytics;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MediaCountQuery
{
    /**
     * Count the number of files (attribute of type pim_catalog_file) that are bind to products.
     * Files that are not bind to products anymore are not taken in account.
     */
    public function countFiles(): int;

    /**
     * Count the number of images (attribute of type pim_catalog_file) that are bind to products.
     * Images that are not bind to products anymore are not taken in account.
     */
    public function countImages(): int;
}
