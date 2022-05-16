<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\Category;

class Category
{
    /** @var integer */
    private $id;

    /** @var integer */
    private $parent_id;

    /** @var integer */
    private $root_id;

    /** @var string */
    private $code;

    /** @var integer */
    private $lft;

    /** @var integer */
    private $rgt;

    /** @var integer */
    private $lvl;

    //
    // links
    //

    /** @var boolean */
    private $isLinked;

    /** @var Category|null */
    private $parent;

    /** @var Category|null */
    private $root;

    /** @Var array */
    private $children;

    public function __construct(array $dbModel)
    {
        $this->id = $dbModel['id'];
        $this->code = $dbModel['code'];

        $this->parent_id = $dbModel['parent_id'];
        $this->root_id = $dbModel['root'];

        $this->lft = $dbModel['lft'];
        $this->rgt = $dbModel['rgt'];
        $this->lvl = $dbModel['lvl'];

        $this->isLinked = false;
        $this->parent = null;
        $this->root = null;
        $this->children = [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getParentId(): ?int
    {
        return $this->parent_id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLeft(): int
    {
        return $this->lft;
    }

    public function setLeft(int $left)
    {
        $this->lft = $left;
    }

    public function getRight(): int
    {
        return $this->rgt;
    }

    public function setRight(int $right)
    {
        $this->rgt = $right;
    }


    public function getLevel(): int
    {
        return $this->lvl;
    }

    public function setLevel(int $level)
    {
        $this->lvl = $level;
    }

    public function getChildren(): iterable
    {
        return $this->children;
    }

    public function getChildAt(int $index): Category
    {
        return $this->children[$index];
    }

    public function link(CategoriesPool $pool)
    {
        if ($this->isLinked) {
            return;
        }

        $this->parent = $pool->find($this->parent_id);
        $this->root = $pool->find($this->root_id);

        $unlinkedChildren = $pool->findForParent($this->id);

        usort($unlinkedChildren, function (Category $c1, Category $c2) {
            return $c1->getLeft() - $c2->getLeft();
        });

        foreach ($unlinkedChildren as $child) {
            $child->link($pool);
            $this->children[] = &$child;
        }

        $this->isLinked = true;
    }

    public function computeNested(int $left = 1, int $level = 0): Category
    {
        $c = clone $this;


        $right = $left;
        foreach ($this->children as $child) {
            $right = $child->computeNested($right + 1, $level + 1)->getRight();
        }

        $c->setLeft($left);
        $c->setRight($right + 1);
        $c->setLevel($level);

        return $c;
    }

    public function diff(Category $c): array
    {
        $diffs = [];
        if ($this->level !== $c->getLevel()) {
            $diffs[] = "Level mismatch ({$this->level} vs {$c->getLevel()})";
        }
        if ($this->lft !== $c->getLeft()) {
            $diffs[] = "Left mismatch ({$this->lft} vs {$c->getLeft()})";
        }
        if ($this->rgt !== $c->getRight()) {
            $diffs[] = "Right mismatch ({$this->rgt} vs {$c->getRight()})";
        }

        if (count($this->children) !== count($c->getChildren())) {
            $diffs[] = "Children count  mismatch ({count($this->children)} vs {count($c->getChildren()})";
        }

        for ($i = 0; $i < count($this->children); $i++) {
            $diffs = array_merge(
                $diffs,
                $this->children[$i]->diff($c->getChildAt($i))
            );
        }

        return $diffs;
    }
}
