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
     * @var ArrayCollection $optionValues
     */
    protected $optionValues;

    /**
     * @var string $localeCode
     */
    protected $localeCode;

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
    public function addOptionValue(AbstractEntityAttributeOptionValue $value)
    {
        $this->optionValues[] = $value;

        return $this;
    }

    /**
     * Remove value
     *
     * @param AbstractEntityAttributeOptionValue $value
     */
    public function removeOptionValue(AbstractEntityAttributeOptionValue $value)
    {
        $this->optionValues->removeElement($value);
    }

    /**
     * Get values
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getOptionValues()
    {
        return $this->optionValues;
    }

    /**
     * Get used locale
     * @return string $locale
     */
    public function getLocaleCode()
    {
        return $this->localeCode;
    }

    /**
     * Set used locale
     * @param string $locale
     */
    public function setLocaleCode($locale)
    {
        $this->localeCode = $locale;
    }
}
