<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete;

class Configuration
{
    /**
     * @var string
     */
    protected $configuration;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Get configuration of autocomplete by name
     *
     * @param string $name
     * @return array
     * @throws \RuntimeException
     */
    public function getAutocompleteOptions($name)
    {
        if (!isset($this->configuration[$name])) {
            throw new \RuntimeException("Autocomplete configuration for \"$name\" is not found");
        }
        $result = $this->configuration[$name];
        if (!empty($result['properties'])) {
            $result['properties'] = $this->createProperties($result['properties']);
        }
        return $result;
    }

    /**
     * @param array $plainProperties
     * @return Property[]
     */
    protected function createProperties(array $plainProperties)
    {
        $result = array();
        foreach ($plainProperties as $options) {
            $result[] = $this->createProperty($options);
        }
        return $result;
    }

    /**
     * @param array $propertyOptions
     * @return Property
     */
    protected function createProperty(array $propertyOptions)
    {
        return new Property($propertyOptions);
    }
}
