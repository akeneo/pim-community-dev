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
     * @var EntitySet $type
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
     * @return EntitySet
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param EntitySet $type
     * @return Entity
     */
    public function setType(EntitySet $type = null)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Add value
     *
     * @param EntityAttributeValue $value
     * @return Entity
     */
    public function addValue(EntityAttributeValue $value)
    {
        $this->values[] = $value;

        return $this;
    }

    /**
     * Remove value
     *
     * @param EntityAttributeValue $value
     */
    public function removeValue(EntityAttributeValue $value)
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