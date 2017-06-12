<?php

namespace Oro\Bundle\DataGridBundle\Datasource;

use Symfony\Component\PropertyAccess\PropertyAccess;

class ResultRecord implements ResultRecordInterface
{
    /** @var array */
    private $valueContainers = [];

    /** @var array */
    private $options = [];

    /**
     * @param mixed $data
     * @param array $options
     */
    public function __construct($data, array $options = [])
    {
        if (is_array($data)) {
            $arrayData = [];
            foreach ($data as $name => $value) {
                if (is_numeric($name) && is_object($value)) {
                    $this->valueContainers[] = $value;
                } else {
                    $arrayData[$name] = $value;
                }
            }
            if ($arrayData) {
                $this->valueContainers[] = $arrayData;
            }
        } elseif (is_object($data)) {
            $this->valueContainers[] = $data;
            $this->options = $options;
        }
    }

    /**
     * Get value of property by name
     *
     * @param  string $name
     *
     * @throws \LogicException When cannot get value
     * @return mixed
     */
    public function getValue($name)
    {
        foreach ($this->valueContainers as $data) {
            if (is_array($data)) {
                if (array_key_exists($name, $data)) {
                    return $data[$name];
                }

                $accessor = PropertyAccess::createPropertyAccessor();

                return $accessor->getValue($data, $name);
            } elseif (is_object($data)) {
                $fieldName = $name;
                $camelizedFieldName = self::camelize($fieldName);
                $getters = [];
                $getters[] = 'get' . $camelizedFieldName;
                $getters[] = 'is' . $camelizedFieldName;

                foreach ($getters as $getter) {
                    if (method_exists($data, $getter)) {
                        return call_user_func([$data, $getter]);
                    }
                }

                if (isset($data->{$fieldName})) {
                    return $data->{$fieldName};
                }

                if (isset($this->options[$name])) {
                    return $this->options[$name];
                }
            }
        }

        throw new \LogicException(sprintf('Unable to retrieve the value of "%s" property', $name));
    }

    /**
     * Camelize a string
     *
     * @static
     *
     * @param  string $property
     *
     * @return string
     */
    private static function camelize($property)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
    }

    /**
     * Gets root entity from result record
     *
     * @return object|null
     */
    public function getRootEntity()
    {
        if (array_key_exists(0, $this->valueContainers) && is_object($this->valueContainers[0])) {
            return $this->valueContainers[0];
        }

        return null;
    }
}
