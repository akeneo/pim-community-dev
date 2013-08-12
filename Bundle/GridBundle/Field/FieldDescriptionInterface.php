<?php

namespace Oro\Bundle\GridBundle\Field;

use Oro\Bundle\GridBundle\Property\PropertyInterface;

interface FieldDescriptionInterface
{
    /**
     * Available field types
     */
    const TYPE_DATE           = 'date';
    const TYPE_DATETIME       = 'datetime';
    const TYPE_DECIMAL        = 'decimal';
    const TYPE_INTEGER        = 'integer';
    const TYPE_OPTIONS        = 'options';
    const TYPE_TEXT           = 'text';
    const TYPE_HTML           = 'html';
    const TYPE_BOOLEAN        = 'boolean';

    /**
     * set the field name
     *
     * @param string $fieldName
     *
     * @return void
     */
    public function setFieldName($fieldName);

    /**
     * return the field name
     *
     * @return string the field name
     */
    public function getFieldName();

    /**
     * Set the name
     *
     * @param string $name
     *
     * @return void
     */
    public function setName($name);

    /**
     * Return the name, the name can be used as a label or table header
     *
     * @return string the name
     */
    public function getName();

    /**
     * Get property for field
     *
     * @return PropertyInterface
     */
    public function getProperty();

    /**
     * Set property for field
     *
     * @param PropertyInterface $property
     */
    public function setProperty(PropertyInterface $property);

    /**
     * Return the value represented by the provided name
     *
     * @param string $name
     * @param null   $default
     *
     * @return array|null the value represented by the provided name
     */
    public function getOption($name, $default = null);

    /**
     * Define an option, an option is has a name and a value
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void set the option value
     */
    public function setOption($name, $value);

    /**
     * Define the options value, if the options array contains the reserved keywords
     *   - type
     *   - template
     *
     * Then the value are copied across to the related property value
     *
     * @param array $options
     *
     * @return void
     */
    public function setOptions(array $options);

    /**
     * return options
     *
     * @return array options
     */
    public function getOptions();

    /**
     * return the template used to render the field
     *
     * @param string $template
     *
     * @return void
     */
    public function setTemplate($template);

    /**
     * return the template name
     *
     * @return string the template name
     */
    public function getTemplate();

    /**
     * return the field type, the type is a mandatory field as it used to select the correct template
     * or the logic associated to the current FieldDescription object
     *
     * @param string $type
     *
     * @return void the field type
     */
    public function setType($type);

    /**
     * return the type
     *
     * @return int|string
     */
    public function getType();

    /**
     * Define the association mapping definition
     *
     * @param array $associationMapping
     *
     * @return void
     */
    public function setAssociationMapping($associationMapping);

    /**
     * return the association mapping definition
     *
     * @return array
     */
    public function getAssociationMapping();

    /**
     * return the related Target Entity
     *
     * @return string|null
     */
    public function getTargetEntity();

    /**
     * set the field mapping information
     *
     * @param array $fieldMapping
     *
     * @return void
     */
    public function setFieldMapping($fieldMapping);

    /**
     * return the field mapping definition
     *
     * @return array the field mapping definition
     */
    public function getFieldMapping();


    /**
     * return true if the FieldDescription is linked to an identifier field
     *
     * @return bool
     */
    public function isIdentifier();

    /**
     * merge option values related to the provided option name
     *
     * @throws \RuntimeException
     *
     * @param string $name
     * @param array  $options
     *
     * @return void
     */
    public function mergeOption($name, array $options = array());

    /**
     * merge options values
     *
     * @param array $options
     *
     * @return void
     */
    public function mergeOptions(array $options = array());

    /**
     * set the original mapping type (only used if the field is linked to an entity)
     *
     * @param string|int $mappingType
     *
     * @return void
     */
    public function setMappingType($mappingType);

    /**
     * return the mapping type
     *
     * @return int|string
     */
    public function getMappingType();

    /**
     * return the label to use for the current field
     *
     * @return string
     */
    public function getLabel();

    /**
     * @return boolean
     */
    public function isSortable();

    /**
     * @return boolean
     */
    public function isFilterable();

    /**
     * Return the field mapping definition used when sorting
     *
     * @return array the field mapping definition
     */
    public function getSortFieldMapping();

    /**
     * Return the parent association mapping definitions used when sorting
     *
     * @return array the parent association mapping definitions
     */
    public function getSortParentAssociationMapping();

    /**
     * @return boolean
     */
    public function isEditable();
}
