<?php
declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\Application\Storage\Update;

use Akeneo\Category\Domain\Model\Category;

interface UpsertCategoryProperties
{
    public function execute(Category $categoryModel): void;
}
