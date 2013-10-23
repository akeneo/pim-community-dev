<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\Association;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Product association entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_catalog_product_association")
 * @ORM\Entity
 */
class ProductAssociation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Association $association
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Entity\Association")
     * @ORM\JoinColumn(name="association_id", nullable=false, onDelete="CASCADE", referencedColumnName="id")
     */
    protected $association;

    /**
     * @var ProductInterface $owner
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Model\ProductInterface", inversedBy="productAssociations")
     * @ORM\JoinColumn(name="owner_id", nullable=false, onDelete="CASCADE", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @var ProductInterface[] $targets
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\CatalogBundle\Model\ProductInterface")
     * @ORM\JoinTable(
     *     name="pim_catalog_product_association_product",
     *     joinColumns={
     *         @ORM\JoinColumn(name="productassociation_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     */
    protected $targets;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->targets = new ArrayCollection();
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
     * @param Association $association
     *
     * @return ProductAssociation
     */
    public function setAssociation(Association $association)
    {
        $this->association = $association;

        return $this;
    }

    /**
     * Get association
     *
     * @return Association
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
     * Set targets
     *
     * @param ProductInterface[] $targets
     *
     * @return ProductAssociation
     */
    public function setTargets($targets)
    {
        $this->targets = $targets;

        return $this;
    }

    /**
     * Get targets
     *
     * @return ProductInterface[]|null
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * Add a target
     *
     * @param ProductInterface $target
     *
     * @return ProductAssociation
     */
    public function addTarget(ProductInterface $target)
    {
        if (!$this->targets->contains($target)) {
            $this->targets->add($target);
        }

        return $this;
    }

    /**
     * Remove a target
     *
     * @param ProductInterface $target
     *
     * @return ProductAssociation
     */
    public function removeTarget(ProductInterface $target)
    {
        $this->targets->removeElement($target);

        return $this;
    }
}
