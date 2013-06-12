<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Transformer;

use Oro\Bundle\FormBundle\EntityAutocomplete\Property;

class EntityPropertiesTransformer implements EntityTransformerInterface
{
    /**
     * @var Property[] $properties
     */
    protected $properties;

    /**
     * @param Property[] $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!$value) {
            return array();
        }

        $data = array(
            'id' => $this->getPropertyValue($this->getIdPropertyName($value), $value)
        );

        foreach ($this->properties as $property) {
            $name = $property->getName();
            $data[$name] = $this->getPropertyValue($name, $value);
        }

        return $data;
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function getIdPropertyName($value)
    {
        if (is_object($value)) {
            // TODO Use doctrine metadata to get name of id property
        }
        return 'id';
    }

    /**
     * @param string $name
     * @param object $value
     * @return string
     */
    protected function getPropertyValue($name, $value)
    {
        $method = 'get' . str_replace(' ', '', str_replace('_', ' ', ucwords($name)));
        if (is_object($value)) {
            if (method_exists($value, $method)) {
                return $value->$method();
            } elseif (isset($value->$name)) {
                return $value->$name;
            }
        } elseif (is_array($value) && array_key_exists($name, $value)) {
            return $value[$name];
        }
        return null;
    }
}
