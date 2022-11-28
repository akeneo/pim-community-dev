<?php

namespace Akeneo\Category\Infrastructure\Cli\CheckCategoryTrees;

use Doctrine\DBAL\Connection;

class Category
{
    private int $id;

    private ?int $parentId;

    private ?int $rootId;

    private string $code;

    private int $lft;

    private int $rgt;

    private int $lvl;

    private bool $isLinked;

    private ?Category $parent;

    private ?Category $root;

    private array $children;

    public function __construct(array $dbModel)
    {
        $this->id = $dbModel['id'];
        $this->code = $dbModel['code'];

        $this->parentId = $dbModel['parent_id'] === null ? null : (int) $dbModel['parent_id'];
        $this->rootId = $dbModel['root'] === null ? null : (int) $dbModel['root'];

        $this->lvl = (int) $dbModel['lvl'];
        $this->lft = (int) $dbModel['lft'];
        $this->rgt = (int) $dbModel['rgt'];

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
        return $this->parentId;
    }

    public function getRootId(): ?int
    {
        return $this->rootId;
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

    public function isLinked(): bool
    {
        return $this->isLinked;
    }

    public function setLevel(int $level)
    {
        $this->lvl = $level;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function addChild(Category $child)
    {
        $this->children[] = $child;
    }

    public function getChildAt(int $index): ?Category
    {
        if (!isset($this->children[$index])) {
            return null;
        }

        return $this->children[$index];
    }

    public function link(CategoriesPool $pool): void
    {
        if ($this->isLinked) {
            return;
        }

        $this->parent = $pool->find($this->parentId);
        $this->root = $pool->find($this->rootId);

        $unlinkedChildren = $pool->findForParent($this->id);

        // make sure that children are sorted by the lft property
        // this will make the ordering basis for (lft,rgt) reordering in ::reorder()
        usort($unlinkedChildren, function (Category $c1, Category $c2) {
            return $c1->getLeft() - $c2->getLeft();
        });

        /** @var Category $child */
        foreach ($unlinkedChildren as $child) {
            $child->link($pool);
            $this->children[] = $child;
        }

        $this->isLinked = true;
    }

    public function reorder(int $left = 1, int $level = 0): Category
    {
        $category = clone $this;
        $category->children = [];

        $right = $left;
        foreach ($this->children as $child) {
            $reorderedChild = $child->reorder($right + 1, $level + 1);
            $right = $reorderedChild->getRight();
            $category->children[] = $reorderedChild;
        }

        $category->setLeft($left);
        $category->setRight($right + 1);
        $category->setLevel($level);

        return $category;
    }

    private function makeDiffError(string $message): string
    {
        return "id={$this->id} code={$this->code} : {$message}";
    }

    public function diff(Category $category): array
    {
        $diffs = [];
        if ($this->lvl !== $category->getLevel()) {
            $diffs[] = $this->makeDiffError("Level mismatch (has:{$this->lvl}, expected:{$category->getLevel()})");
        }
        if ($this->lft !== $category->getLeft()) {
            $diffs[] = $this->makeDiffError("Left mismatch (has:{$this->lft}, expected:{$category->getLeft()})");
        }
        if ($this->rgt !== $category->getRight()) {
            $diffs[] = $this->makeDiffError("Right mismatch (has:{$this->rgt}, expected:{$category->getRight()})");
        }

        if (count($this->children) !== count($category->getChildren())) {
            $diffs[] = $this->makeDiffError(
                sprintf(
                    'Children count mismatch (has:%s, expected:%s)',
                    count($this->children),
                    count($category->getChildren()),
                ),
            );
        }

        for ($i = 0; $i < count($this->children); ++$i) {
            if ($category->getChildAt($i)) {
                $childrenDiffErrors = $this->children[$i]->diff($category->getChildAt($i));

                $childrenDiffErrorsWithContext = array_map(
                    function ($childDiff) use ($i) {
                        return "Child at index $i: $childDiff";
                    },
                    $childrenDiffErrors,
                );

                $diffs = array_merge(
                    $diffs,
                    $childrenDiffErrorsWithContext,
                );
            }
        }

        return $diffs;
    }

    public function dumpNodes($level = 0, $maxLevel = 1): array
    {
        $spaces = str_repeat("\t", $level);
        $rows = ["{$spaces}({$this->id},{$this->code},lvl={$this->lvl},lft={$this->lft},rgt={$this->rgt})"];
        if ($level < $maxLevel) {
            foreach ($this->children as $child) {
                $rows = array_merge(
                    $rows,
                    $child->dumpNodes($level + 1),
                );
            }
        }

        return $rows;
    }

    public function doUpdate(Connection $connection)
    {
        $connection->update(
            'pim_catalog_category',
            [
                'lvl' => $this->lvl,
                'lft' => $this->lft,
                'rgt' => $this->rgt,
            ],
            [
                'id' => $this->id,
            ],
        );

        foreach ($this->children as $child) {
            $child->doUpdate($connection);
        }
    }
}
