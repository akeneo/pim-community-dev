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
    public function setType(EntityType $type = null)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Add value
     *
     * @param EntityValue $value
     * @return Entity
     */
    public function addValue(EntityValue $value)
    {
        $this->values[] = $value;

        return $this;
    }

    /**
     * Remove value
     *
     * @param EntityValue $value
     */
    public function removeValue(EntityValue $value)
    {
        $this->values->removeElement($value);
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