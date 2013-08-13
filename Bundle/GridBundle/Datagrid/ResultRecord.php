<?php

namespace Oro\Bundle\GridBundle\Datagrid;

class ResultRecord implements ResultRecordInterface
{
    /**
     * @var mixed
     */
    private $data;

    /**
     * @var array
     */
    private $valueContainers;

    /**
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;

        $this->valueContainers = array();
        if (is_array($data)) {
            $arrayData = array();
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
        }
    }

    /**
     * Get value of property by name
     *
     * @param string $name
     * @return mixed
     * @throws \LogicException When cannot get value
     */
    public function getValue($name)
    {
        foreach ($this->valueContainers as $data) {
            if (is_array($data) && array_key_exists($name, $data)) {
                return $data[$name];
            } elseif (is_object($data)) {
                $fieldName = $name;
                $camelizedFieldName = self::camelize($fieldName);
                $getters = array();
                $getters[] = 'get' . $camelizedFieldName;
                $getters[] = 'is' . $camelizedFieldName;

                foreach ($getters as $getter) {
                    if (method_exists($data, $getter)) {
                        return call_user_func(array($data, $getter));
                    }
                }

                if (isset($data->{$fieldName})) {
                    return $data->{$fieldName};
                }
            }
        }

        throw new \LogicException(sprintf('Unable to retrieve the value of "%s" property', $name));
    }

    /**
     * Camelize a string
     *
     * @static
     * @param string $property
     * @return string
     */
    private static function camelize($property)
    {
        return preg_replace(
            array('/(^|_| )+(.)/e', '/\.(.)/e'),
            array("strtoupper('\\2')", "'_'.strtoupper('\\1')"),
            $property
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getRootEntity()
    {
        // if only entity was selected
        if (is_object($this->data)) {
            return $this->data;
        }

        // if array is returned root entity must be under 0 key
        if (is_array($this->data) && array_key_exists(0, $this->data) && is_object($this->data[0])) {
            return $this->data[0];
        }

        return null;
    }
}
