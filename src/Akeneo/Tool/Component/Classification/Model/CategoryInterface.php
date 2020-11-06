<?php

namespace Akeneo\Tool\Component\Classification\Model;

use Doctrine\Common\Collections\Collection;

/**
 * Category interface
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CategoryInterface
{
    public function getId(): int;

    /**
     * @param string $code
     */
    public function setCode(string $code): \Akeneo\Tool\Component\Classification\Model\CategoryInterface;

    public function getCode(): string;

    /**
     * @param int $left
     */
    public function setLeft(int $left): \Akeneo\Tool\Component\Classification\Model\CategoryInterface;

    public function getLeft(): int;

    /**
     * @param int $level
     */
    public function setLevel(int $level): \Akeneo\Tool\Component\Classification\Model\CategoryInterface;

    public function getLevel(): int;

    /**
     * @param int $right
     */
    public function setRight(int $right): \Akeneo\Tool\Component\Classification\Model\CategoryInterface;

    public function getRight(): int;

    /**
     * @param int $root
     */
    public function setRoot(int $root): \Akeneo\Tool\Component\Classification\Model\CategoryInterface;

    public function getRoot(): int;

    /**
     * @param CategoryInterface $parent
     */
    public function setParent(CategoryInterface $parent = null): \Akeneo\Tool\Component\Classification\Model\CategoryInterface;

    public function getParent(): ?\Akeneo\Tool\Component\Classification\Model\CategoryInterface;

    /**
     * If a node is a tree root, it's the tree starting point and therefore
     * defines the tree itself.
     */
    public function isRoot(): bool;

    /**
     * Add a child to this category
     *
     * @param CategoryInterface $child
     */
    public function addChild(CategoryInterface $child): \Akeneo\Tool\Component\Classification\Model\CategoryInterface;

    /**
     * Remove a child from this category
     *
     * @param CategoryInterface $child
     */
    public function removeChild(CategoryInterface $child): \Akeneo\Tool\Component\Classification\Model\CategoryInterface;

    /**
     * Predicate to know if this category has children
     */
    public function hasChildren(): bool;

    /**
     * Get children of this category
     */
    public function getChildren(): Collection;
}
