<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\Category;

class CategoriesPool // implements \Stringable
{
    /** @var array */
    private $categories;

    public function __construct(array $dbModels) {
        $this->categories = [];

        foreach ($dbModels as $dbModel) {
            $this->categories[] = new Category($dbModel);
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
        foreach ($this->categories as $c) {
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

//    public function __toString(): string {
//        return "pool: {count($this->categories)} categories";
//    }
//
//    public function __debugInfo() {
//        return [
//            'size' => count($this->categories),
//        ];
//    }
}
