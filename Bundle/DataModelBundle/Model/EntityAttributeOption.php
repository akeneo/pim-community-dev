<?php
namespace Oro\Bundle\DataModelBundle\Model;

/**
 * Abstract entity attribute option, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT
 */
abstract class EntityAttributeOption
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $value
     */
    protected $value;

    /**
     * @var integer $sortOrder
     */
    protected $sortOrder;

    /**
     * @var string $locale
     */
    protected $locale;

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
     * @return EntityAttributeValue
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return EntityAttributeValue
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set sort order
     *
     * @param string $sortOrder
     *
     * @return EntityAttributeValue
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
     * Set used locale
     * @param string $locale
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}
