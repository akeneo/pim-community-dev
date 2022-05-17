<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\Category;

use Doctrine\DBAL\Connection;

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

        $this->parent_id = $dbModel['parent_id'] === null ? null : (int)$dbModel['parent_id'];
        $this->root_id = $dbModel['root'] === null ? null : (int)$dbModel['root'];

        $this->lft = (int)$dbModel['lft'];
        $this->rgt = (int)$dbModel['rgt'];
        $this->lvl = (int)$dbModel['lvl'];

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

    public function getChildren(): array
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

        // make sure that children are sorted by the lft property
        // this will make the ordering basis for (lft,rgt) reordering in ::reorder()
        usort($unlinkedChildren, function (Category $c1, Category $c2) {
            return $c1->getLeft() - $c2->getLeft();
        });

        foreach ($unlinkedChildren as $child) {
            $child->link($pool);
            $this->children[] = $child;
        }

        $this->isLinked = true;
    }

    public function reorder(int $left = 1, int $level = 0): Category
    {
        $c = clone $this;
        $c->children = [];

        $right = $left;
        foreach ($this->children as $child) {
            $reorderedChild = $child->reorder($right + 1, $level + 1);
            $right = $reorderedChild->getRight();
            $c->children[] = $reorderedChild;
        }

        $c->setLeft($left);
        $c->setRight($right + 1);
        $c->setLevel($level);

        return $c;
    }

    private function makeDiffError(string $message): string
    {
        return "id={$this->id} code={$this->code}  : {$message}";
    }

    public function diff(Category $c): array
    {
        $diffs = [];
        if ($this->lvl !== $c->getLevel()) {
            $diffs[] = $this->makeDiffError("Level mismatch (has:{$this->lvl}, expected:{$c->getLevel()})");
        }
        if ($this->lft !== $c->getLeft()) {
            $diffs[] = $this->makeDiffError("Left mismatch (has:{$this->lft}, expected:{$c->getLeft()})");
        }
        if ($this->rgt !== $c->getRight()) {
            $diffs[] = $this->makeDiffError("Right mismatch (has:{$this->rgt}, expected:{$c->getRight()})");
        }

        if (count($this->children) !== count($c->getChildren())) {
            $diffs[] = $this->makeDiffError("Children count mismatch (has:{count($this->children)}, expected:{count($c->getChildren()})");
        }

        //var_export($this->children);

        for ($i = 0; $i < count($this->children); $i++) {
            $childrenDiffErrors = $this->children[$i]->diff($c->getChildAt($i));

            $childrenDiffErrorsWithContext = array_map(
                function ($childDiff) use ($i) {
                    return "Child at index {$i}: ${childDiff}";
                },
                $childrenDiffErrors
            );

            $diffs = array_merge(
                $diffs,
                $childrenDiffErrorsWithContext
            );
        }

        return $diffs;
    }

    public function dumpNodes($level = 0, $maxLevel = 1): array
    {
        $spaces = str_repeat("\t", $level);
        $rows = ["{$spaces}({$this->id},{$this->code},lvl={$this->lvl},lft={$this->lft},rgt={$this->rgt})"];
        if ($level < $maxLevel) {
            foreach ($this->children as $child) {
                $rows = array_merge($rows,
                    $child->dumpNodes($level + 1)
                );
            }
        }
        return $rows;
    }

    public function doUpdate(Connection $connection)
    {
        $connection->update(
            "pim_catalog_category",
            [
                "lvl" => $this->lvl,
                "lft" => $this->lft,
                "rgt" => $this->rgt,
            ], [
            "id" => $this->id
        ],

        );

        foreach ($this->children as $child) {
            $child->doUpdate($connection);
        }
    }
}
