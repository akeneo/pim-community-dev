<?php
namespace Oro\Bundle\DataModelBundle\Model;

/**
 * Abstract entity attribute option, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
abstract class AbstractEntityAttributeOption
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var integer $sortOrder
     */
    protected $sortOrder;

    /**
     * @var ArrayCollection $values
     */
    protected $values;

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
     * Set id
     *
     * @param integer $id
     *
     * @return AbstractEntityAttributeOption
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set sort order
     *
     * @param string $sortOrder
     *
     * @return AbstractEntityAttributeOption
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Get sort order
     *
     * @return integer
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Add value
     *
     * @param AbstractEntityAttributeOptionValue $value
     *
     * @return Entity
     */
    public function addValue(AbstractEntityAttributeOptionValue $value)
    {
        $this->values[] = $value;

        return $this;
    }

    /**
     * Remove value
     *
     * @param AbstractEntityAttributeOptionValue $value
     */
    public function removeValue(AbstractEntityAttributeOptionValue $value)
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
