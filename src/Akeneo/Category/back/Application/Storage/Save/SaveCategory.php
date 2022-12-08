<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\Application\Storage\Save;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Domain\Model\Enrichment\Category;

interface SaveCategory
{
    /**
     * @param UserIntent[] $userIntents
     */
    public function save(Category $category, array $userIntents): void;
}
