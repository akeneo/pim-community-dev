<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Transformer;

use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FormBundle\EntityAutocomplete\Property;

class EntityPropertiesTransformer implements EntityTransformerInterface
{
    /**
     * @var array $properties
     */
    protected $propertyNames;

    /**
     * @param array|Property[] $properties
     */
    public function __construct(array $properties)
    {
        $this->propertyNames = array();
        foreach ($properties as $property) {
            if ($property instanceof Property) {
                $property = $property->getName();
            }
            $this->propertyNames[] = $property;
        }
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

        foreach ($this->propertyNames as $name) {
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
        $result = null;

        if (is_object($value)) {
            $method = 'get' . str_replace(' ', '', str_replace('_', ' ', ucwords($name)));
            if (method_exists($value, $method)) {
                $result = $value->$method();
            } elseif (isset($value->$name)) {
                $result = $value->$name;
            }
        } elseif (is_array($value) && array_key_exists($name, $value)) {
            $result = $value[$name];
        }

        if ($result instanceof FlexibleValueInterface) {
            $result = $result->getData();
        }

        return $result;
    }
}
