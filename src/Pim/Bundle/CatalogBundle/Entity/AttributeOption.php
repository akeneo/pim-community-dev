<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Model\ReferableInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Attribute options
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
class AttributeOption implements ReferableInterface
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $code
     */
    protected $code;

    /**
     * Overrided to change target entity name
     *
     * @var \Pim\Bundle\CatalogBundle\Model\AbstractAttribute $attribute
     */
    protected $attribute;

    /**
     * @var ArrayCollection $values
     */
    protected $optionValues;

    /**
     * Specifies whether this AttributeOption is the default option for the attribute
     *
     * @var boolean $default
     */
    protected $default = false;

    /**
     * @var boolean
     */
    protected $translatable;

    /**
     * Not persisted, allowe to define the value locale
     * @var string $locale
     */
    protected $locale;

    /**
     * @var integer
     */
    protected $sortOrder;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->optionValues = new ArrayCollection();
        $this->translatable = true;
        $this->sortOrder    = 1;
    }

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
     * @return AttributeOption
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return AbstractAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return AttributeOption
     */
    public function setAttribute(AbstractAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get values
     *
     * @return \ArrayAccess
     */
    public function getOptionValues()
    {
        return $this->optionValues;
    }

    /**
     * Get used locale
     *
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set used locale
     *
     * @param string $locale
     *
     * @return LocalizableInterface
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Set translatable
     *
     * @param boolean $translatable
     *
     * @return AttributeOption
     */
    public function setTranslatable($translatable)
    {
        $this->translatable = $translatable;

        return $this;
    }

    /**
     * Is translatable
     *
     * @return boolean $translatable
     */
    public function isTranslatable()
    {
        return $this->translatable;
    }

    /**
     * Set sort order
     *
     * @param string $sortOrder
     *
     * @return AttributeOption
     */
    public function setSortOrder($sortOrder)
    {
        if ($sortOrder !== null) {
            $this->sortOrder = $sortOrder;
        }

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
     * Set code
     *
     * @param string $code
     *
     * @return AttributeOption
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set default
     * @param boolean $default
     *
     * @return AttributeOption
     */
    public function setDefault($default)
    {
        $this->default = (bool) $default;

        return $this;
    }

    /**
     * Get default
     *
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * Override to use default value
     *
     * @return string
     */
    public function __toString()
    {
        $value = $this->getOptionValue();

        return ($value and $value->getValue()) ? $value->getValue() : '['.$this->getCode().']';
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return ($this->attribute ? $this->attribute->getCode() : '') . '.' . $this->code;
    }

    /**
     * Returns the current translation
     *
     * @return AttributeOptionValue
     */
    public function getTranslation()
    {
        $value = $this->getOptionValue();

        if (!$value) {
            $value = new AttributeOptionValue();
            $value->setLocale($this->locale);
            $this->addOptionValue($value);
        }

        return $value;
    }

    /**
     * Add option value
     *
     * @param AttributeOptionValue $value
     *
     * @return AbstractAttribute
     */
    public function addOptionValue(AttributeOptionValue $value)
    {
        $this->optionValues[] = $value;
        $value->setOption($this);

        return $this;
    }

    /**
     * Remove value
     *
     * @param AttributeOptionValue $value
     *
     * @return AttributeOption
     */
    public function removeOptionValue(AttributeOptionValue $value)
    {
        $this->optionValues->removeElement($value);

        return $this;
    }

    /**
     * Get localized value
     *
     * @return AbstractEntityAttributeOptionValue
     */
    public function getOptionValue()
    {
        $translatable = $this->translatable;
        $locale = $this->getLocale();
        $values = $this->getOptionValues()->filter(
            function ($value) use ($translatable, $locale) {
                // return relevant translated value
                if ($translatable and $value->getLocale() == $locale) {
                    return true;
                } elseif (!$translatable) {
                    return true;
                }
            }
        );
        $value = $values->first();

        return $value;
    }
}
