<?php

namespace Pim\Component\Catalog\Model;

use Akeneo\Component\Classification\CategoryAwareInterface;
use Akeneo\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Product model interface.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ProductModelInterface extends
    EntityWithValuesInterface,
    TimestampableInterface,
    VersionableInterface,
    CategoryAwareInterface
{
    /**
     * Gets the ID of the product model.
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Gets the identifier of the product model.
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Sets the product model identifier.
     *
     * @param ValueInterface $identifier
     *
     * @return ProductModelInterface
     *
     */
    public function setIdentifier(ValueInterface $identifier): ProductModelInterface;

    /**
     * Gets the products of the product model.
     *
     * @return Collection
     */
    public function getProducts(): Collection;

    /**
     * Adds an product to the product model.
     *
     * @param ProductInterface $product
     *
     * @throws \LogicException
     *
     * @return ProductModelInterface
     */
    public function addProduct(ProductInterface $product): ProductModelInterface;

    /**
     * Removes an product from the product model.
     *
     * @param ProductInterface $product
     *
     * @return ProductModelInterface
     */
    public function removeProduct(ProductInterface $product): ProductModelInterface;

    /**
     * @param int $root
     *
     * @return ProductModelInterface
     */
    public function setRoot(int $root): ProductModelInterface;

    /**
     * @return int
     */
    public function getRoot(): int;

    /**
     * If a node is a tree root, it's the tree starting point and therefore
     * defines the tree itself.
     *
     * @return bool
     */
    public function isRoot(): bool;

    /**
     * @param int $level
     *
     * @return ProductModelInterface
     */
    public function setLevel(int $level): ProductModelInterface;

    /**
     * @return int
     */
    public function getLevel(): int;

    /**
     * @param int $left
     *
     * @return ProductModelInterface
     */
    public function setLeft(int $left): ProductModelInterface;

    /**
     * @return int
     */
    public function getLeft(): int;

    /**
     * @param int $right
     *
     * @return ProductModelInterface
     */
    public function setRight(int $right): ProductModelInterface;

    /**
     * @return int
     */
    public function getRight(): int;

    /**
     * Sets the parent of this product model.
     *
     * @param ProductModelInterface $parent
     *
     * @return ProductModelInterface
     */
    public function setParent(ProductModelInterface $parent = null): ProductModelInterface;

    /**
     * Gets the parent of this product model.
     *
     * @return ProductModelInterface|null
     */
    public function getParent(): ?ProductModelInterface;

    /**
     * Adds a child to this product model.
     *
     * @param ProductModelInterface $child
     *
     * @return ProductModelInterface
     */
    public function addChild(ProductModelInterface $child): ProductModelInterface;

    /**
     * Removes a child from this product model.
     *
     * @param ProductModelInterface $child
     *
     * @return ProductModelInterface
     */
    public function removeChild(ProductModelInterface $child): ProductModelInterface;

    /**
     * Predicates to know if this product model has children.
     *
     * @return bool
     */
    public function hasChildren(): bool;

    /**
     * Gets the children of this product model.
     *
     * @return Collection
     */
    public function getChildren(): Collection;
}
