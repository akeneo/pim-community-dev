<?php

namespace Akeneo\Tool\Component\Classification\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Implementation of CategoryInterface
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

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode(string $code): \Akeneo\Tool\Component\Classification\Model\CategoryInterface
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setLeft(int $left): \Akeneo\Tool\Component\Classification\Model\CategoryInterface
    {
        $this->left = $left;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLeft(): int
    {
        return $this->left;
    }

    /**
     * {@inheritdoc}
     */
    public function setLevel(int $level): \Akeneo\Tool\Component\Classification\Model\CategoryInterface
    {
        $this->level = $level;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * {@inheritdoc}
     */
    public function setRight(int $right): \Akeneo\Tool\Component\Classification\Model\CategoryInterface
    {
        $this->right = $right;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRight(): int
    {
        return $this->right;
    }

    /**
     * {@inheritdoc}
     */
    public function setRoot(int $root): \Akeneo\Tool\Component\Classification\Model\CategoryInterface
    {
        $this->root = $root;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoot(): int
    {
        return $this->root;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(CategoryInterface $parent = null): \Akeneo\Tool\Component\Classification\Model\CategoryInterface
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?\Akeneo\Tool\Component\Classification\Model\CategoryInterface
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(CategoryInterface $child): \Akeneo\Tool\Component\Classification\Model\CategoryInterface
    {
        $child->setParent($this);
        $this->children[] = $child;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild(CategoryInterface $children): \Akeneo\Tool\Component\Classification\Model\CategoryInterface
    {
        $this->children->removeElement($children);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren(): bool
    {
        return count($this->getChildren()) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren(): \Doctrine\Common\Collections\Collection
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function isRoot(): bool
    {
        return (null === $this->getParent());
    }
}
