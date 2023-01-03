<?php

namespace Akeneo\Pim\Permission\Bundle\ServiceApi\Category;

use Akeneo\Pim\Permission\Bundle\Category\GetCategoryProductPermissions;

class GetCategoryProductPermissionsByCategoryId implements GetCategoryProductPermissionsByCategoryIdInterface
{
    public function __construct(
        private readonly GetCategoryProductPermissions $getCategoryProductPermissions
    ) {
    }

    /**
     * @param int $categoryId
     * @return array
     * ex.
     *    [
     *      "view" => [ ["id" => 1, "label" => "IT Support"], ["id" => 3, "label" => "Redactor"] ],
     *      "edit" => [ ["id" => 1, "label" => "IT Support"], ["id" => 3, "label" => "Redactor"] ],
     *      "own" => [ ["id" => 1, "label" => "IT Support"], ["id" => 3, "label" => "Redactor"] ],
     *    ]
     */
    public function __invoke(int $categoryId): array
    {
        return ($this->getCategoryProductPermissions)($categoryId);
    }
}
