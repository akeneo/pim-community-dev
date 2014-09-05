<?php

namespace Pim\Bundle\EnrichBundle\Entity;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * SequentialEdit entity
 *
 * @author    Rémy Bétus <remy.betus@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SequentialEdit
{
    /** @var integer $id */
    protected $id;

    /** @var integer[] $productSet */
    protected $productSet = [];

    /** @var UserInterface $user */
    protected $user;

    /** @var ProductInterface */
    protected $current;

    /** @var ProductInterface */
    protected $previous;

    /** @var ProductInterface */
    protected $next;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return SequentialEdit
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set user
     *
     * @param UserInterface $user
     *
     * @return SequentialEdit
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get product set
     *
     * @return integer[]
     */
    public function getProductSet()
    {
        return $this->productSet;
    }

    /**
     * Set product set
     *
     * @param integer[] $productSet
     *
     * @return SequentialEdit
     */
    public function setProductSet(array $productSet)
    {
        $this->productSet = $productSet;

        return $this;
    }

    /**
     * @param ProductInterface $current
     *
     * @return SequentialEdit
     */
    public function setCurrent(ProductInterface $current)
    {
        $this->current = $current;

        return $this;
    }

    /**
     * @return ProductInterface
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * @param ProductInterface $next
     *
     * @return SequentialEdit
     */
    public function setNext(ProductInterface $next = null)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * @return ProductInterface
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @param ProductInterface $previous
     *
     * @return SequentialEdit
     */
    public function setPrevious(ProductInterface $previous = null)
    {
        $this->previous = $previous;

        return $this;
    }

    /**
     * @return ProductInterface
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * Count number of products to edit
     *
     * @return integer
     */
    public function countProductSet()
    {
        return count($this->productSet);
    }

    /**
     * Search the number of indexed products
     * TODO: Be sure that it's never called with an unknown id
     *
     * @param ProductInterface $product
     *
     * @return integer
     */
    public function countEditedProducts(ProductInterface $product)
    {
        return array_search($product->getId(), $this->productSet) + 1;
    }

    /**
     * Get the next Product id from a product
     *
     * @param integer $productId
     *
     * @return integer
     */
    public function getNextId($productId)
    {
        $nextKey = array_search($productId, $this->productSet) + 1;

        return $this->productSet[$nextKey];
    }
}
