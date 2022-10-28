<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Storage;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Domain\Model\Enrichment\Category;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProcessCategoryUpdateMock
{
    /**
     * @param array<UserIntent> $userIntents
     */
    public function update(Category $category, array $userIntents): void
    {
    }
}
