<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Storage\Update;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Domain\Model\Category;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CategoryUpdater
{
    /**
     * @return UserIntent[]
     */
    public function getSupportedUserIntents(): array;

    public function update(Category $categoryModel): void;
}
