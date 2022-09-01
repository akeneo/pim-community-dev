<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Filter;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;

/**
 * Filters user intents according to the use case. Example: cannot change code in update context.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryEditUserIntentFilter
{
    /**
     * @param userIntent[] $collection
     *
     * @return userIntent[]
     */
    public function filterCollection(array $collection): array
    {
        return $collection;
    }
}
