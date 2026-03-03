<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\Api;

interface FindGrantedCategoryTrees
{
    /**
     * @return CategoryTree[]
     */
    public function execute(): array;
}
