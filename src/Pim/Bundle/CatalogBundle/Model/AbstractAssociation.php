<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Group;

/**
 * Abstract association entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
abstract class AbstractAssociation implements ReferableInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var AssociationType $associationType
     */
    protected $associationType;

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
     * @var array
     */
    protected $groupIds = [];

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
     * Set association type
     *
     * @param AssociationType $associationType
     *
     * @return AbstractAssociation
     */
    public function setAssociationType(AssociationType $associationType)
    {
        $this->associationType = $associationType;

        return $this;
    }

    /**
     * Get association type
     *
     * @return AssociationType
     */
    public function getAssociationType()
    {
        return $this->associationType;
    }

    /**
     * Set owner
     *
     * @param ProductInterface $owner
     *
     * @return AbstractAssociation
     */
    public function setOwner(ProductInterface $owner)
    {
        if (!$this->owner) {
            $this->owner = $owner;
            $owner->addAssociation($this);
        }

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
     * @return AbstractAssociation
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
     * @return AbstractAssociation
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
     * @return AbstractAssociation
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
     * @return AbstractAssociation
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Get groups
     *
     * @return Group[]
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
     * @return AbstractAssociation
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
     * @return AbstractAssociation
     */
    public function removeGroup(Group $group)
    {
        $this->groups->removeElement($group);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->owner ? $this->owner->getIdentifier() . '.' . $this->associationType->getCode() : null;
    }
}
