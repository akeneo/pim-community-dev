<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

use Doctrine\Common\Collections\ArrayCollection;

class EntityConfig extends AbstractConfig implements EntityConfigInterface
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var FieldConfig[]|ArrayCollection
     */
    protected $fields;

    public function __construct($className, $scope)
    {
        $this->fields    = new ArrayCollection();
        $this->className = $className;
        $this->scope     = $scope;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setClassName($name)
    {
        $this->className = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return string|void
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param FieldConfig[] $fields
     * @return $this
     */
    public function setFields($fields)
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }

        return $this;
    }

    /**
     * @param FieldConfig $field
     */
    public function addField(FieldConfig $field)
    {
        $this->fields[$field->getCode()] = $field;
    }

    /**
     * @param $name
     * @return FieldConfig
     */
    public function getField($name)
    {
        return $this->fields[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasField($name)
    {
        return isset($this->fields[$name]);
    }

    /**
     * @param  callable $filter
     * @return FieldConfig[]|ArrayCollection
     */
    public function getFields(\Closure $filter = null)
    {
        return $filter ? $this->fields->filter($filter) : $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->className,
            $this->scope,
            $this->fields,
            $this->values,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->className,
            $this->scope,
            $this->fields,
            $this->values,
            ) = unserialize($serialized);
    }

    /**
     * Clone Config
     */
    public function __clone()
    {
        $this->values = array_map(function ($value) {
            if (is_object($value)) {
                return clone $value;
            } else {
                return $value;
            }
        }, $this->values);

        $this->fields = $this->fields->map(function ($field) {
            return clone $field;
        }, $this->fields);
    }
}
