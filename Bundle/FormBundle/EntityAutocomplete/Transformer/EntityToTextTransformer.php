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

        $data = array();
        /** @var $property Property */
        foreach ($options['properties'] as $property) {
            $data[] = $this->getPropertyValue($property->getName(), $value);
        }

        return implode(' ', $data);
    }

    /**
     * @param string $name
     * @param object $object
     * @return string
     */
    protected function getPropertyValue($name, $object)
    {
        $method = 'get' . str_replace(' ', '', str_replace('_', ' ', ucwords($name)));
        if (method_exists($object, $method)) {
            return $object->$method();
        }
        return '';
    }
}
