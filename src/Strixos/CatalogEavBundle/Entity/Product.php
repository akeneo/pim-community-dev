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
}