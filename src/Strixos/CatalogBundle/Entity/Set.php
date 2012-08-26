<?php

namespace Strixos\CatalogBundle\Entity;

use Strixos\CoreBundle\Model\AbstractModel;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Strixos\CatalogBundle\Entity\Set
 *
 * @ORM\Table(name="StrixosCatalog_Set")
 * @ORM\Entity
 */
class Set extends AbstractModel
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
    private $code;

    /**
    * @ORM\ManyToMany(targetEntity="Attribute")
    * @ORM\JoinTable(name="StrixosCatalog_Set_Attribute",
    *      joinColumns={@ORM\JoinColumn(name="attribute_id", referencedColumnName="id")},
    *      inverseJoinColumns={@ORM\JoinColumn(name="attributeset_id", referencedColumnName="id")}
    *      )
    */
    protected $attributes;

    /**
    * @ORM\ManyToMany(targetEntity="Group")
    * @ORM\JoinTable(name="StrixosCatalog_Set_Group",
    *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
    *      inverseJoinColumns={@ORM\JoinColumn(name="attributeset_id", referencedColumnName="id")}
    *      )
    */
    protected $groups;

    /**
    * Constructor
    */
    public function __construct()
    {
        $this->attributes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
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
        $copySet = new Set();
        $copySet->setCode($newCode);
        foreach ($this->getAttributes() as $attribute) {
            $copySet->addAttribute($attribute);
        }
        foreach ($this->getGroups() as $groupToCopy) {
/*            $copyGroup = new Group();
            $copyGroup->setCode($groupToCopy->getCode());
            $copyGroup->setAttributeSet($copySet);*/
            $copySet->addGroup($groupToCopy);
        }
        return $copySet;
    }

    /**
     * Add groups
     *
     * @param Strixos\CatalogBundle\Entity\Group $groups
     * @return AttributeSet
     */
    public function addGroup(\Strixos\CatalogBundle\Entity\Group $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param Strixos\CatalogBundle\Entity\Group $groups
     */
    public function removeGroup(\Strixos\CatalogBundle\Entity\Group $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }
}