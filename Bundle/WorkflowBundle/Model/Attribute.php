<?php

namespace Oro\Bundle\WorkflowBundle\Model;

class Attribute
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $formTypeName;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * Set form type name.
     *
     * @param string $formType
     * @return Attribute
     */
    public function setFormTypeName($formType)
    {
        $this->formTypeName = $formType;
        return $this;
    }

    /**
     * Get form type name.
     *
     * @return string
     */
    public function getFormTypeName()
    {
        return $this->formTypeName;
    }

    /**
     * Set attribute label.
     *
     * @param string $label
     * @return Attribute
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Get attribute label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set attribute name.
     *
     * @param string $name
     * @return Attribute
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get attribute name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set options.
     *
     * @param array $options
     * @return Attribute
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set option by key.
     *
     * @param string $key
     * @param mixed $value
     * @return Attribute
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * Get option by key.
     *
     * @param string $key
     * @return null|mixed
     */
    public function getOption($key)
    {
        return $this->hasOption($key) ? $this->options[$key] : null;
    }

    /**
     * Check for option availability by key.
     *
     * @param string $key
     * @return bool
     */
    public function hasOption($key)
    {
        return isset($this->options[$key]);
    }
}
