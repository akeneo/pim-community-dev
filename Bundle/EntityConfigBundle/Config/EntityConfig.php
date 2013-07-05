<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

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
     * @var FieldConfig[]
     */
    protected $fields = array();

    public function __construct($className, $scope)
    {
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
     * @param  callable      $filter
     * @return FieldConfig[]
     */
    public function getFields(\Closure $filter = null)
    {
        return $filter ? array_filter($this->fields, $filter) : $this->fields;
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
}
