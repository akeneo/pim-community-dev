<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete;

class SearchPropertyConfig
{
    const OPERATOR_TYPE_CONTAINS = 'contains';
    const OPERATOR_TYPE_START_WITH = 'start_with';

    /**
     * @var string
     */
    protected $property;

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
    public function getProperty()
    {
        return $this->getRequiredOption('property');
    }

    /**
     * @return string
     */
    public function getOperatorType()
    {
        return $this->getOption('operator_type', self::OPERATOR_TYPE_CONTAINS);
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
     * @throws \LogicException
     */
    public function getRequiredOption($name)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        } else {
            throw new \LogicException("Option '$name' of search property config has no value.");
        }
    }
}
