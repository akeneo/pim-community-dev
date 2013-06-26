<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfigInterface;

abstract class AbstractConfig
{
    /**
     * @var ConfigValue[]|ArrayCollection
     */
    protected $values;

    /**
     * @param ConfigValue[] $values
     * @return $this
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @param ConfigValue $value
     * @return $this
     */
    public function addValue($value)
    {
        if ($this instanceof EntityConfigInterface) {
            $value->setEntity($this);
        } else {
            $value->setField($this);
        }

        $this->values->add($value);

        return $this;
    }

    /**
     * @param  callable $filter
     * @return array|ArrayCollection|ConfigValue[]
     */
    public function getValues(\Closure $filter = null)
    {
        return $filter ? array_filter($this->values->toArray(), $filter) : $this->values;
    }

    /**
     * @param $code
     * @param $scope
     * @return ConfigValue
     */
    public function getValue($code, $scope)
    {
        $values = $this->getValues(function (ConfigValue $value) use ($code, $scope) {
            return ($value->getScope() == $scope && $value->getCode() == $code);
        });

        return reset($values);
    }

    /**
     * @param $scope
     * @return array
     */
    public function toArray($scope)
    {
        $values = $this->getValues(function (ConfigValue $value) use ($scope) {
            return $value->getScope() == $scope;
        });

        $result = array();
        foreach ($values as $value) {
            $result[$value->getCode()] = $value->getValue();
        }

        return $result;
    }

    /**
     * @param       $scope
     * @param array $values
     */
    public function fromArray($scope, array $values)
    {
        foreach ($values as $code => $value) {
            if ($configValue = $this->getValue($code, $scope)) {
                $configValue->setValue($value);
            } else {
                $configValue = new ConfigValue($code, $scope, $value);
                $this->addValue($configValue);
            }
        }
    }
}
