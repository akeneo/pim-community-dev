<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete;

class Property
{
    /**
     * @var string
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getRequiredOption('name');
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }
        return $default;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \RuntimeException
     */
    public function getRequiredOption($name)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        } else {
            throw new \RuntimeException("Property option \"$name\" is required.");
        }
    }
}
