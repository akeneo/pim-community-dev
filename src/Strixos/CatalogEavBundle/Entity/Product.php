<?php

namespace Strixos\CatalogEavBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Bap\FlexibleEntityBundle\Model\Entity;

/**
 * @author     Romain Monceau @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="StrixosCatalogEav_Product_Entity")
 * @ORM\Entity
 */
class Product extends Entity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var EntityType $type
     *
     * @ORM\ManyToOne(targetEntity="Type")
     */
    protected $type;
    
    /**
     * @var Value
     * 
     * @ORM\OneToMany(targetEntity="Value", mappedBy="product")
     */
    protected $values;
    
    /**
     * magic getter hehe
     * @param unknown $property
     * @return mixed
     */
    public function __get($property)
    {
        /*if (isset($this->$property))
        {
            return $this->$property;
        }
        else return null;*/
    }
    
    /**
     * magic setter =D
     * @param unknown $property
     * @param unknown $value
     */
    public function __set($property, $value)
    {
        echo 'call setter';
    }
    
    public function __call($name, $arguments)
    {
        /*echo '<br />';
        echo 'name -> '. $name .'<br />';
        echo 'arguments '. $arguments .'<hr />';*/
        
        switch (substr($name, 0, 3)) {
            case 'get':
                
                $fieldCode = strtolower(substr($name, 3));
                
                foreach ($this->getType()->getFields() as $field)
                {
                    if ($field->getCode() == $fieldCode)
                    {
                        foreach ($this->getValues() as $value)
                        {
                            if ($value->getField()->getId() == $field->getId())
                            {
                                return $value->getContent();
                            }
                        }
                    }
                }
                throw new \Exception('exception  !');
            case 'set':
                
                $fieldCode = strtolower(substr($name, 3));
                
                echo 'search field code .. : '. $fieldCode .'<br />';
                
                foreach ($this->getType()->getFields() as $field) {
                    if ($field->getCode() == $fieldCode) {
                        foreach ($this->getValues() as $value) {
                            
                            
                            echo 'value : '. $value->getContent() .'<br />';
                            
                            if ($value->getField()->getId() == $field->getId()) {
                                echo 'OKKKKKK<br />';
                                return $value->setContent($arguments[0]);
                            }
                        }

                        // add value
                        $value = new Value();
                        $value->setField($field);
                        $value->setProduct($this);
                        $value->setContent($arguments[0]);
                        break;
                    }
                }
                
                throw new \Exception('field '. $fieldCode .' not exist !');
        }
        
        
        echo '<br />----- END of call method -----';
        echo '<hr />';
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
     * Set type
     *
     * @param Strixos\CatalogEavBundle\Entity\Type $type
     * @return Product
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
     * Constructor
     */
    public function __construct()
    {
        $this->values = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add values
     *
     * @param Strixos\CatalogEavBundle\Entity\Value $values
     * @return Product
     */
    public function addValue(\Strixos\CatalogEavBundle\Entity\Value $values)
    {
        $this->values[] = $values;
    
        return $this;
    }

    /**
     * Remove values
     *
     * @param Strixos\CatalogEavBundle\Entity\Value $values
     */
    public function removeValue(\Strixos\CatalogEavBundle\Entity\Value $values)
    {
        $this->values->removeElement($values);
    }

    /**
     * Get values
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getValues()
    {
        return $this->values;
    }
}