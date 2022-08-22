<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Query;

use Akeneo\Category\Domain\ValueObject\CategoryId;

/**
 * Getting information about a category.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryQuery
{
    /**
     * @param CategoryId $id the id of the category to get
     */
    public function __construct(
        private CategoryId $id,
    ) {
    }

    public function categoryId(): CategoryId
    {
        return $this->id;
    }
}
