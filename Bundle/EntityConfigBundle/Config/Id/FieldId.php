<?php

namespace Oro\Bundle\EntityConfigBundle\Config\Id;

class FieldId implements FieldIdInterface
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
    public function __construct($className, $scope, $fieldName, $fieldType)
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
     * @return string
     */
    public function getId()
    {
        return 'field_' . $this->scope . '_' . $this->className . '_' . $this->fieldName;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'Config for Entity "%s" Field "%s" in scope "%s"',
            $this->getClassName(),
            $this->getFieldName(),
            $this->getScope()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->className,
            $this->scope,
            $this->fieldName,
            $this->fieldType,
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
            $this->fieldName,
            $this->fieldType,
            ) = unserialize($serialized);
    }
}
