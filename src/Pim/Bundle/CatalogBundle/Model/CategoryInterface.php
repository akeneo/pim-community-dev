<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\VersioningBundle\Model\VersionableInterface;

/**
 * Category interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CategoryInterface extends TranslatableInterface, ReferableInterface, VersionableInterface
{
    /**
     * @return int|string
     */
    public function getId();

    /**
     * @param string $code
     *
     * @return CategoryInterface
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param integer $left
     *
     * @return CategoryInterface
     */
    public function setLeft($left);

    /**
     * @return integer
     */
    public function getLeft();

    /**
     * @param integer $level
     *
     * @return CategoryInterface
     */
    public function setLevel($level);

    /**
     * @return integer
     */
    public function getLevel();

    /**
     * @param integer $right
     *
     * @return CategoryInterface
     */
    public function setRight($right);

    /**
     * @return integer
     */
    public function getRight();

    /**
     * @param integer $root
     *
     * @return CategoryInterface
     */
    public function setRoot($root);

    /**
     * @return integer
     */
    public function getRoot();

    /**
     * @param CategoryInterface $parent
     *
     * @return CategoryInterface
     */
    public function setParent(CategoryInterface $parent = null);

    /**
     * @return CategoryInterface|null
     */
    public function getParent();

    /**
     * If a node is a tree root, it's the tree starting point and therefore
     * defines the tree itself.
     *
     * @return boolean
     */
    public function isRoot();

    /**
     * Add a child to this category
     *
     * @param CategoryInterface $child
     *
     * @return CategoryInterface
     */
    public function addChild(CategoryInterface $child);

    /**
     * Remove a child from this category
     *
     * @param CategoryInterface $child
     *
     * @return CategoryInterface
     */
    public function removeChild(CategoryInterface $child);

    /**
     * Predicate to know if this category has children
     *
     * @return boolean
     */
    public function hasChildren();

    /**
     * Get children of this category
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren();

    /**
     * Predicate to know if this category has product(s) linked
     *
     * @return boolean
     */
    public function hasProducts();

    /**
     * Get products for this category node
     *
     * @return ProductInterface[]
     */
    public function getProducts();
}
