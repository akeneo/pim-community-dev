<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Transformer;

use Oro\Bundle\FormBundle\EntityAutocomplete\Property;
use Oro\Bundle\FormBundle\EntityAutocomplete\Configuration;

class EntityToTextTransformer implements EntityTransformerInterface
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string $alias
     * @param mixed $value
     * @return string
     */
    public function transform($alias, $value)
    {
        $options = $this->configuration->getAutocompleteOptions($alias);
        if (!$value || !is_array($options) || !array_key_exists('properties', $options) || !is_array($options['properties'])) {
            return '';
        }

        $data = array();
        /** @var $property Property */
        foreach ($options['properties'] as $property) {
            $data[] = $this->getPropertyValue($property->getName(), $value);
        }

        return trim(implode(' ', $data));
    }

    /**
     * @param string $name
     * @param object $value
     * @return string
     */
    protected function getPropertyValue($name, $value)
    {
        $method = 'get' . str_replace(' ', '', str_replace('_', ' ', ucwords($name)));
        if (is_object($value) && method_exists($value, $method)) {
            return $value->$method();
        } elseif (is_array($value) && array_key_exists($name, $value)) {
            return $value[$name];
        }
        return '';
    }
}
