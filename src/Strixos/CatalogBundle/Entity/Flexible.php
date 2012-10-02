<?php

namespace Strixos\CatalogBundle\Entity;

use Strixos\CoreBundle\Model\AbstractModel;

/**
 * 
 * @author Romain Monceau @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * Strixos\CatalogBundle\Entity\Flexible
 * 
 * TODO : maybe mustn't extends AbstractModel.. see later
 */
class Flexible extends AbstractModel
{
    /**
     * @var integer $id
     */
    private $id;
    
    /**
     * @var EntityType
     */
    private $type;
    
    /**
     * Get id
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get type EntityType
     * @return \Strixos\CatalogBundle\Entity\EntityType
     */
    public function getType()
    {
        return $this->type;
    }
}