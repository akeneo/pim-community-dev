<?php

namespace Strixos\CatalogEavBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Bap\FlexibleEntityBundle\Model\EntityType;

/**
 * @author     Romain Monceau @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="StrixosCatalogEav_Product_Type")
 * @ORM\Entity
 */
class Type extends EntityType
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
     * @var ArrayCollection $fields
     * @ORM\ManyToMany(targetEntity="Field")
     * @ORM\JoinTable(name="StrixosCatalogEav_Product_Type_Field")
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
     * @return Type
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
     * Add fields
     *
     * @param Strixos\CatalogEavBundle\Entity\Field $fields
     * @return Type
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