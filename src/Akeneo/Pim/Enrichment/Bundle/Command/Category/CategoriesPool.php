<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\Category;

class CategoriesPool
{
    /** @var array */
    private $categories;

    public function __construct(array $dbModels) {
        $this->categories = [];

        foreach ($dbModels as $dbModel) {
            $id = (int)$dbModel['id'];
            $this->categories[$id] = new Category($dbModel);
        }

    }

    public function find(?int $id):?Category {
        if (is_null($id)) {
            return null;
        }
        $c = $this->categories[$id];
        return $c;
    }

    public function getRoots(): iterable {
        $roots = [];
        foreach ($this->categories as $id =>$c) {
            if (is_null($c->getParentId())) {
                $roots[] = clone $c;
            }
        }
        return $roots;
    }

    public function findForParent(int $parent_id): iterable {
        $children = [];
        foreach ($this->categories as $c) {
            if ($c->getParentId() === $parent_id) {
                $children[] = clone $c;
            }
        }
        return $children;
    }

}
