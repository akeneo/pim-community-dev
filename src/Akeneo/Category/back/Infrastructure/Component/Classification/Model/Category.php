<?php

namespace Akeneo\Category\Infrastructure\Component\Classification\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Implementation of CategoryInterface.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Category implements CategoryInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $code;

    /** @var int */
    protected $left;

    /** @var int */
    protected $level;

    /** @var int */
    protected $right;

    /** @var int */
    protected $root;

    /** @var CategoryInterface */
    protected $parent;

    /** @var Collection */
    protected $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setLeft($left)
    {
        $this->left = $left;

        return $this;
    }

    public function getLeft()
    {
        return $this->left;
    }

    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setRight($right)
    {
        $this->right = $right;

        return $this;
    }

    public function getRight()
    {
        return $this->right;
    }

    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function setParent(?CategoryInterface $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function addChild(CategoryInterface $child)
    {
        $child->setParent($this);
        $this->children[] = $child;

        return $this;
    }

    public function removeChild(CategoryInterface $children)
    {
        $this->children->removeElement($children);

        return $this;
    }

    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function isRoot()
    {
        return null === $this->getParent();
    }
}
