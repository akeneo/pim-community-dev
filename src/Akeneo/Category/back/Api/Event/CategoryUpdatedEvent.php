<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Event;

use Akeneo\Category\Domain\Model\Enrichment\Category;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CategoryUpdatedEvent
{
    /**
     * @param Category $category The category updated
     * @param Category $previousCategory The original category
     */
    public function __construct(
        private readonly Category $category,
        private readonly Category $previousCategory
    ) {
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getPreviousCategory(): Category
    {
        return $this->previousCategory;
    }
}
