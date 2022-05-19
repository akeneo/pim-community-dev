<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\Category;

class CategoriesPool
{
    private array $categories;

    public function __construct(array $dbModels)
    {
        $this->categories = [];

        foreach ($dbModels as $dbModel) {
            $id = (int)$dbModel['id'];
            $this->categories[$id] = new Category($dbModel);
        }
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function find(?int $id): ?Category
    {
        if (is_null($id)) {
            return null;
        }

        return $this->categories[$id];
    }

    public function getRoots(): iterable
    {
        $roots = [];
        /** @var Category $category */
        foreach ($this->categories as $category) {
            if (is_null($category->getParentId())) {
                $roots[] = clone $category;
            }
        }

        return $roots;
    }

    public function findForParent(int $parent_id): iterable
    {
        $children = [];
        /** @var Category $category */
        foreach ($this->categories as $category) {
            if ($category->getParentId() === $parent_id) {
                $children[] = clone $category;
            }
        }

        return $children;
    }
}
