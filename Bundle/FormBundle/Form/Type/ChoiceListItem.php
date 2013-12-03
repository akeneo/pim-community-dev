<?php

namespace Oro\Bundle\FormBundle\Form\Type;

/**
 * Allows to provide additional attributes for 'option' element of 'choice' form type
 */
class ChoiceListItem
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var array
     */
    private $attributes;

    /**
     * Constructor
     *
     * @param string $label      A string is used as a label of the choice
     * @param array  $attributes Additional attributes of the choice
     */
    public function __construct($label = null, array $attributes = array())
    {
        $this->label      = $label;
        $this->attributes = $attributes;
    }

    /**
     * Returns a label of the choice
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets a label of the choice
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Returns additional attributes of the choice.
     *
     * @return array An array of key-value combinations.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns additional attributes of the choice.
     * This method is just an alias for getAttributes and it intended to use in Twig, for example choice.attr
     *
     * @return array An array of key-value combinations.
     */
    public function getAttr()
    {
        return $this->attributes;
    }

    /**
     * Returns whether the attribute with the given name exists.
     *
     * @param string $name The attribute name.
     *
     * @return Boolean Whether the attribute exists.
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Returns the value of the given attribute.
     *
     * @param string $name    The attribute name.
     * @param mixed  $default The value returned if the attribute does not exist.
     *
     * @return mixed The attribute value.
     */
    public function getAttribute($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    /**
     * Sets the value for an attribute.
     *
     * @param string $name  The name of the attribute
     * @param string $value The value of the attribute
     *
     * @return self The configuration object.
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Sets the attributes.
     *
     * @param array $attributes The attributes.
     *
     * @return self The configuration object.
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Returns a label of the choice
     *
     * @return string
     */
    public function __toString()
    {
        return $this->label;
    }
}
