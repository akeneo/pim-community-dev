<?php

namespace Oro\Bundle\EntityConfigBundle\Config\Id;

class FieldConfigId implements ConfigIdInterface
{
    /**
     * @var string
     */
    protected $scope;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var string
     */
    protected $fieldType;

    /**
     * @param $className
     * @param $scope
     * @param $fieldName
     * @param $fieldType
     */
    public function __construct($className, $scope, $fieldName, $fieldType = null)
    {
        $this->className = $className;
        $this->scope     = $scope;
        $this->fieldName = $fieldName;
        $this->fieldType = $fieldType;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }


    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * @param string $fieldType
     * @return $this
     */
    public function setFieldType($fieldType)
    {
        $this->fieldType = $fieldType;

        return $this;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return sprintf('field_%s_%s_%s', $this->scope, strtr($this->className, '\\', '-'), $this->fieldName);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            array(
                $this->className,
                $this->scope,
                $this->fieldName,
                $this->fieldType,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->className,
            $this->scope,
            $this->fieldName,
            $this->fieldType,
            ) = unserialize($serialized);
    }
}
