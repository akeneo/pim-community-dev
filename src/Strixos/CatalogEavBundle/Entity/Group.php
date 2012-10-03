<?php

namespace Strixos\CatalogEavBundle\Entity;

use Bap\FlexibleEntityBundle\Model\EntityFieldGroup;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="StrixosCatalogEav_Product_Group")
 * @ORM\Entity
 */
class Group extends EntityFieldGroup
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
     * TODO only unique for one type !
     *
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    private $code;

    /**
     * @var EntityType $type
     *
     * @ORM\ManyToOne(targetEntity="Type")
     */
    protected $type;

    /**
     * @var ArrayCollection $fields
     * @ORM\ManyToMany(targetEntity="Field")
     * @ORM\JoinTable(name="StrixosCatalogEav_Product_Group_Field")
     */
    private $fields;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fields = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set type
     *
     * @param Strixos\CatalogEavBundle\Entity\Type $type
     * @return Group
     */
    public function setType(\Strixos\CatalogEavBundle\Entity\Type $type = null)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return Strixos\CatalogEavBundle\Entity\Type 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add fields
     *
     * @param Strixos\CatalogEavBundle\Entity\Field $fields
     * @return Group
     */
    public function addField(\Strixos\CatalogEavBundle\Entity\Field $fields)
    {
        $this->fields[] = $fields;
    
        return $this;
    }

    /**
     * Remove fields
     *
     * @param Strixos\CatalogEavBundle\Entity\Field $fields
     */
    public function removeField(\Strixos\CatalogEavBundle\Entity\Field $fields)
    {
        $this->fields->removeElement($fields);
    }

    /**
     * Get fields
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getFields()
    {
        return $this->fields;
    }
}