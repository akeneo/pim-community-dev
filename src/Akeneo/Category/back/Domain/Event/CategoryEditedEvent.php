<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Event;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Domain\Model\Enrichment\Category;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CategoryEditedEvent
{
    /**
     * @param array<UserIntent> $userIntents
     */
    public function __construct(
        private readonly Category $category,
        private readonly array $userIntents,
    ) {
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @return array<UserIntent>
     */
    public function getUserIntents(): array
    {
        return $this->userIntents;
    }
}
