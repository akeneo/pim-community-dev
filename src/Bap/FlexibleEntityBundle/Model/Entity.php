<?php
namespace Bap\FlexibleEntityBundle\Model;

/**
 * Entity
 *
 * @author    Nicolas Dupont @ Strixos
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Entity
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var EntityType $type
     */
    protected $type;

    /**
     * Get system id
     * @return integer $i
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get entity type
     * @return EntityType
     */
    public function getType()
    {
        return $this->type;
    }

}