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
     * @var EntitySet $set
     */
    protected $set;

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
     * Get set
     *
     * @return EntitySet
     */
    public function getSet()
    {
        return $this->set;
    }

    /**
     * Set set
     *
     * @param EntitySet $set
     * @return Entity
     */
    public function setSet(EntitySet $set = null)
    {
        $this->set = $set;
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