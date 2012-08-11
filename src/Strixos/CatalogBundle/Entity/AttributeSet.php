<?php

namespace Strixos\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Strixos\CatalogBundle\Entity\AttributeSet
 *
 * @ORM\Table(name="StrixosCatalog_AttributeSet")
 * @ORM\Entity
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

    /**
    * Add an attribute to the set
    *
    * @param Poc\StrixosCatalogBundle\Entity\Attribute $attribute
    */
    public function addAttribute($attribute)
    {
        $this->attributes[] = $attribute;
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
     * @return AttributeSet
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
     * Copy an attribute set
     *
     * @return AttributeSet $set
     */
    public function copy($newCode)
    {
        // TODO just unset id not works (due to lazy loading ?)
        $copy = new AttributeSet();
        $copy->setCode($newCode);
        foreach ($this->getAttributes() as $attribute) {
            $copy->addAttribute($attribute);
        }
        return $copy;
    }
}