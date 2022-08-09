<?php

declare(strict_types=1);

/**
 * A GetCategoryModel represents the information returned by the GetCategoryQuery query.
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\Api\Model;

use Akeneo\Category\Api\Model\Category\Category;

class CategoryReadModel
{
    public function __construct(
        private Category $category,
        private Permissions $permissions,
        private AttributeValues $attributeValues
    ) {
    }

    public function category(): Category
    {
        return $this->category;
    }

    public function permissions(): Permissions
    {
        return $this->permissions;
    }

    public function attributeValues(): AttributeValues
    {
        return $this->attributeValues;
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(): array
    {
        return [
            'properties' => $this->category->normalize(),
            'attributes' => $this->attributeValues->normalize(),
            'permissions' => $this->permissions->normalize(),
        ];
    }
}
