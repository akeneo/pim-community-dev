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

    /** @var integer[] $objectSet */
    protected $objectSet = [];

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
    public function getObjectSet()
    {
        return $this->objectSet;
    }

    /**
     * Set product set
     *
     * @param integer[] $objectSet
     *
     * @return SequentialEdit
     */
    public function setObjectSet(array $objectSet)
    {
        $this->objectSet = $objectSet;

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
    public function countObjectSet()
    {
        return count($this->objectSet);
    }

    /**
     * Search the number of indexed products
     *
     * @param ProductInterface $product
     *
     * @return integer
     */
    public function countEditedProducts(ProductInterface $product)
    {
        return array_search($product->getId(), $this->objectSet) + 1;
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
        $nextKey = array_search($productId, $this->objectSet) + 1;

        return $this->objectSet[$nextKey];
    }
}
