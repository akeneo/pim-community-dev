<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
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
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Model\ProductInterface", inversedBy="associations")
     * @ORM\JoinColumn(name="product_id", nullable=false, onDelete="CASCADE", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @var ProductInterface $target
     *
     * @ORM\OneToOne(targetEntity="Pim\Bundle\CatalogBundle\Model\ProductInterface")
     * @ORM\JoinColumn(name="target_id", nullable=false, onDelete="CASCADE", referencedColumnName="id")
     */
    protected $target;

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
     * Set target
     *
     * @param ProductInterface $target
     *
     * @return ProductAssociation
     */
    public function setTarget(ProductInterface $target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return ProductInterface
     */
    public function getTarget()
    {
        return $this->target;
    }
}
