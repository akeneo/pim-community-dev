<?php

namespace Strixos\CatalogBundle\Entity\Flexible;

use Strixos\CoreBundle\Model\AbstractModel;

/**
 * 
 * @author Romain Monceau @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * Strixos\CatalogBundle\Entity\Flexible\Type
 * 
 * TODO : maybe mustn't extends AbstractModel.. see later
 */
class Type extends AbstractModel
{
    /**
     * @var integer $code
     */
    private $typeCode;
    
    /**
     * Get type code
     * 
     * @param integer $code
     */
    public function getTypeCode($code)
    {
        
    }
    
    /**
     * Set type code
     * 
     * @param integer $code
     */
    public function setTypeCode($code)
    {
        $this->code = $code;
    }
    
    /**
     * Add attribute 
     * @param integer $code
     * @param unknown $type
     * @param unknown $fieldGroup
     * @param string $isMultiValue
     */
    /*public function addAttribute($code, $type, $fieldGroup, $isMultiValue = false)
    {
        // TODO
    }*/
    
    public function addAttributeGroup($code)
    {
        
    }
    
    public function getAttributeGroup($code)
    {
        
    }
    
    public function removeAttributeGroup($code, $forceIfNotEmpty = false)
    {
        
    }
    
    public function addAttribute($code)
    {
        
    }
    
    public function removeAttribute($code)
    {
        
    }
    
    public function newFlexibleEntityInstance()
    {
        
    }
}