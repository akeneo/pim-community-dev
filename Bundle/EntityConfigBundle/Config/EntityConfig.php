<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

use Doctrine\Common\Collections\Expr\Value;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigField;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigValue;

class EntityConfig implements ConfigInterface
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var FieldConfig[]
     */
    protected $fields = array();

    /**
     * @var ValueConfig[]
     */
    protected $values = array();

    public function __construct($className)
    {
        $this->className = $className;
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
     * @param FieldConfig[] $fields
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @param FieldConfig $field
     */
    public function addField(FieldConfig $field)
    {
        $this->fields[$field->getName()] = $field;
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
     * @param ValueConfig[] $values
     * @return $this
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @param ValueConfig $value
     */
    public function addValue(ValueConfig $value)
    {
        $this->values[] = $value;
    }

    /**
     * @param  callable            $filter
     * @return array|ValueConfig[]
     */
    public function getValues(\Closure $filter = null)
    {
        return $filter ? array_filter($this->values, $filter) : $this->values;
    }

    /**
     * @param  ConfigEntity $entity
     * @return EntityConfig
     */
    public static function fromEntity(ConfigEntity $entity)
    {
        $config = new self($entity->getClassName());

        array_map(function (ConfigValue $value) use ($config) {
            $valueConfig = new ValueConfig();
            $valueConfig->setScope($value->getScope())
                ->setCode($value->getCode())
                ->setValue($value->getValue());
            $config->addValue($valueConfig);
        }, $entity->getValues()->toArray());

        // feel entity config field
        array_map(function (ConfigField $filed) use ($config) {
            $fieldConfig = new FieldConfig();
            $fieldConfig->setName($filed->getCode());

            // feel field config values
            array_map(function (ConfigValue $value) use ($fieldConfig) {
                $valueConfig = new ValueConfig();
                $valueConfig->setScope($value->getScope())
                    ->setCode($value->getCode())
                    ->setValue($value->getValue());
                $fieldConfig->addValue($valueConfig);
            }, $filed->getValues()->toArray());

            $config->addField($fieldConfig);
        }, $entity->getFields()->toArray());

        return $config;
    }

    /**
     * @param ConfigEntity $entity
     */
    public function toEntity(ConfigEntity $entity = null)
    {
        if (!$entity) {
            $entity = new ConfigEntity;
        }

        $entity->setClassName($this->getClassName());

        // feel entity values
        array_map(function (ValueConfig $value) use ($entity) {
            $configValue = new ConfigValue();
            $configValue->setScope($value->getScope())
                ->setCode($value->getCode())
                ->setValue($value->getValue());
            $entity->addValue($configValue);
        }, $this->getValues());

        // feel entity field
        array_map(function (FieldConfig $filed) use ($entity) {
            $configField = new ConfigField();
            $configField->setCode($filed->getName());

            // feel field  values
            array_map(function (ValueConfig $value) use ($configField) {
                $configValue = new ConfigValue();
                $configValue->setScope($value->getScope())
                    ->setCode($value->getCode())
                    ->setValue($value->getValue());
                $configField->addValue($configValue);
            }, $filed->getValues());

            $entity->addFiled($configField);
        }, $this->getFields());
    }

    /**
     * @param $scope
     * @return EntityConfig
     */
    public function cloneFilteredByScope($scope)
    {
        $config = new self($this->getClassName());
        $config->setFields(array_merge(array(), $this->getFields(function (FieldConfig $field) use ($scope) {
                return count($field->getValues(function (ValueConfig $field) use ($scope) {
                    return $field->getScope() == $scope;
                }));
            })
        ));

        $config->setValues(array_merge(array(), $this->getValues(function (ValueConfig $field) use ($scope) {
                return $field->getScope() == $scope;
            })
        ));

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->className,
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
            $this->fields,
            $this->values,
            ) = unserialize($serialized);
    }
}
