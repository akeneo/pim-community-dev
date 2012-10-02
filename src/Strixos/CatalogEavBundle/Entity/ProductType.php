<?php

namespace Strixos\CatalogEavBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Bap\FlexibleEntityBundle\Model\EntityType;

/**
 * @author Romain Monceau @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="StrixosCatalogEav_ProductType")
 * @ORM\Entity
 */
class ProductType extends EntityType
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    protected $code;
    
    /**
     * @var ArrayCollection $products
     * @ORM\OneToMany(targetEntity="Product", mappedBy="type", cascade={"persist", "remove"})
     */
    private $products;
    
    /**
     * @var ArrayCollection $attributes
     * @ORM\ManyToMany(targetEntity="Attribute")
     * @ORM\JoinTable(name="StrixosCatalogEav_ProductType_Attribute")
     */
    private $attributes;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set code
     *
     * @param string $code
     * @return ProductType
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Add products
     *
     * @param Strixos\CatalogEavBundle\Entity\Product $products
     * @return ProductType
     */
    public function addProduct(\Strixos\CatalogEavBundle\Entity\Product $products)
    {
        $this->products[] = $products;
    
        return $this;
    }

    /**
     * Remove products
     *
     * @param Strixos\CatalogEavBundle\Entity\Product $products
     */
    public function removeProduct(\Strixos\CatalogEavBundle\Entity\Product $products)
    {
        $this->products->removeElement($products);
    }

    /**
     * Get products
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Add attributes
     *
     * @param Strixos\CatalogEavBundle\Entity\Attribute $attributes
     * @return ProductType
     */
    public function addAttribute(\Strixos\CatalogEavBundle\Entity\Attribute $attributes)
    {
        $this->attributes[] = $attributes;
    
        return $this;
    }

    /**
     * Remove attributes
     *
     * @param Strixos\CatalogEavBundle\Entity\Attribute $attributes
     */
    public function removeAttribute(\Strixos\CatalogEavBundle\Entity\Attribute $attributes)
    {
        $this->attributes->removeElement($attributes);
    }

    /**
     * Get attributes
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}