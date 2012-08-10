<?php

namespace Strixos\CatalogCatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Strixos\CatalogBundle\Entity\Attribute
 *
 * @ORM\Entity
 * @ORM\Table(name="StrixosCatalog_AttributeSet")
*/
class AttributeSet
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
     * @ORM\ManyToMany(targetEntity="Attribute")
     * @ORM\JoinTable(name="StrixosCatalog_AttributeSet_Attribute",
     *      joinColumns={@ORM\JoinColumn(name="attribute_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="attributeset_id", referencedColumnName="id")}
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
    * @return string
    */
    public function getId()
    {
        return $this->id;
    }

    /**
    * Set code
    *
    * @param string $code
    */
    public function setCode($code)
    {
        $this->code = $code;
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
     * Add an attribute to the set
     *
     * @param Poc\StrixosCatalogBundle\Entity\Attribute $attribute
     */
    public function addAttribute($attribute)
    {
        $this->attributes[] = $attribute;
    }

}