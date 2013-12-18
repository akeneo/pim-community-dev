<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOption;
use Pim\Bundle\CatalogBundle\Model\ReferableEntityInterface;

/**
 * Attribute options
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
class AttributeOption extends AbstractEntityAttributeOption implements ReferableEntityInterface
{
    /**
     * @var string $code
     */
    protected $code;

    /**
     * Overrided to change target entity name
     *
     * @var Attribute $attribute
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
        return $this->attribute->getCode() . '.' . $this->code;
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
}
