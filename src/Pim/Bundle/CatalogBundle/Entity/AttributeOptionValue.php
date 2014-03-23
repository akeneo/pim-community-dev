<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use JMS\Serializer\Annotation\ExclusionPolicy;

/**
 * Attribute option values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
class AttributeOptionValue
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var AttributeOption $option
     */
    protected $option;

    /**
     * Locale scope
     * @var string $locale
     */
    protected $locale;

    /**
     * @var string $value
     */
    protected $value;

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
     * @return AttributeOptionValue
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set option
     *
     * @param AttributeOption $option
     *
     * @return AttributeOptionValue
     */
    public function setOption(AttributeOption $option)
    {
        $this->option = $option;

        return $this;
    }

    /**
     * Get option
     *
     * @return AttributeOption
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Get used locale
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set used locale
     * @param string $locale
     *
     * @return AttributeOptionValue
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return AttributeOptionValue
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
     * Returns the label of the attribute
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->value;
    }

    /**
     * Sets the label
     *
     * @param string $label
     *
     * @return AttributeOptionValue
     */
    public function setLabel($label)
    {
        $this->value = $label;

        return $this;
    }
}
