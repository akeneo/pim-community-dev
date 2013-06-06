<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Transformer;

use Oro\Bundle\FormBundle\EntityAutocomplete\Property;

class EntityToTextTransformer implements EntityTransformerInterface
{
    /**
     * @param mixed $value
     * @param Property[] $properties
     * @return string
     */
    public function transform($value, array $properties)
    {
        $data = array();

        foreach ($properties as $property) {
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
        if (is_object($value)) {
            if (method_exists($value, $method)) {
                return $value->$method();
            } elseif (isset($value->$name)) {
                return $value->$name;
            }
        } elseif (is_array($value) && array_key_exists($name, $value)) {
            return $value[$name];
        }
        return '';
    }
}
