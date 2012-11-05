<?php

namespace Strixos\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Strixos\CatalogBundle\Entity\Attribute
 *
 * @ORM\Table(name="StrixosCatalog_Group")
 * @ORM\Entity
 */
class Group
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
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="Set")
     */
    protected $set;

    /**
    * @ORM\ManyToMany(targetEntity="Attribute")
    * @ORM\JoinTable(name="StrixosCatalog_Group_Attribute",
    *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
    *      inverseJoinColumns={@ORM\JoinColumn(name="attribute_id", referencedColumnName="id")}
    *      )
     */
    protected $attributes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Group
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
     * Set set
     *
     * @param Strixos\CatalogBundle\Entity\Set $set
     * @return Group
     */
    public function setSet(\Strixos\CatalogBundle\Entity\Set $set = null)
    {
        $this->set = $set;

        return $this;
    }

    /**
     * Get set
     *
     * @return Strixos\CatalogBundle\Entity\Set
     */
    public function getSet()
    {
        return $this->set;
    }

    /**
     * Add attributes
     *
     * @param Strixos\CatalogBundle\Entity\Attribute $attributes
     * @return Group
     */
    public function addAttribute(\Strixos\CatalogBundle\Entity\Attribute $attributes)
    {
        $this->attributes[] = $attributes;

        return $this;
    }

    /**
     * Remove attributes
     *
     * @param Strixos\CatalogBundle\Entity\Attribute $attributes
     */
    public function removeAttribute(\Strixos\CatalogBundle\Entity\Attribute $attributes)
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