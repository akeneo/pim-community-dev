<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\Application\Storage\Save\Query;

use Akeneo\Category\Domain\Model\Enrichment\Category;

interface UpsertCategoryBase
{
    public function execute(Category $categoryModel): void;
}
