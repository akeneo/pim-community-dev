<?php
namespace Bap\Bundle\FlexibleEntityBundle\Model;

/**
 * Abstract entity, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get type
     *
     * @return EntityType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param EntityType $type
     * @return Entity
     */
    public function setType($type = null)
    {
        $this->type = $type;
        return $this;
    }

}