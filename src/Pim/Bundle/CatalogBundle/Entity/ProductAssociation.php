<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Product association entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
class ProductAssociation
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var AssociationType $association
     */
    protected $association;

    /**
     * @var ProductInterface $owner
     */
    protected $owner;

    /**
     * @var ProductInterface[] $products
     */
    protected $products;

    /**
     * @var Group[] $groups
     */
    protected $groups;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }

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
     * Set association
     *
     * @param AssociationType $association
     *
     * @return ProductAssociation
     */
    public function setAssociation(AssociationType $association)
    {
        $this->association = $association;

        return $this;
    }

    /**
     * Get association
     *
     * @return AssociationType
     */
    public function getAssociation()
    {
        return $this->association;
    }

    /**
     * Set owner
     *
     * @param ProductInterface $owner
     *
     * @return ProductAssociation
     */
    public function setOwner(ProductInterface $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return ProductInterface
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set products
     *
     * @param ProductInterface[] $products
     *
     * @return ProductAssociation
     */
    public function setProducts($products)
    {
        $this->products = $products;

        return $this;
    }

    /**
     * Get products
     *
     * @return ProductInterface[]|null
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Add a product
     *
     * @param ProductInterface $product
     *
     * @return ProductAssociation
     */
    public function addProduct(ProductInterface $product)
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    /**
     * Remove a product
     *
     * @param ProductInterface $product
     *
     * @return ProductAssociation
     */
    public function removeProduct(ProductInterface $product)
    {
        $this->products->removeElement($product);

        return $this;
    }

    /**
     * Set groups
     *
     * @param Group[] $groups
     *
     * @return ProductAssociation
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Get groups
     *
     * @return Group[]|null
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Add a group
     *
     * @param Group $group
     *
     * @return ProductAssociation
     */
    public function addGroup(Group $group)
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    /**
     * Remove a group
     *
     * @param Group $group
     *
     * @return ProductAssociation
     */
    public function removeGroup(Group $group)
    {
        $this->groups->removeElement($group);

        return $this;
    }
}
