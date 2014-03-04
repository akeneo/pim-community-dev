<?php

namespace Pim\Bundle\FlexibleEntityBundle\Model;

use Pim\Bundle\FlexibleEntityBundle\Model\Behavior\TimestampableInterface;

/**
 * Abstract entity attribute, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttribute implements TimestampableInterface
{
    /**
     * Attribute id
     * @var integer $id
     */
    protected $id;

    /**
     * Attribute code
     * @var string $code
     */
    protected $code;

    /**
     * Attribute label
     * @var string $label
     */
    protected $label;

    /**
     * Entity type (FQCN)
     * @var string $entityType
     */
    protected $entityType;

    /**
     * Attribute type (service alias))
     * @var string $attributeType
     */
    protected $attributeType;

    /**
     * Kind of storage to store values
     * @var string $backendStorage
     */
    protected $backendStorage;

    /**
     * Kind of field to store values
     * @var string $backendType
     */
    protected $backendType;

    /**
     * @var datetime $created
     */
    protected $created;

    /**
     * @var datetime $created
     */
    protected $updated;

    /**
     * Is attribute is required
     * @var boolean $required
     */
    protected $required;

    /**
     * Is attribute value is required
     * @var boolean $unique
     */
    protected $unique;

    /**
     * Default attribute value
     * @var string $defaultValue
     */
    protected $defaultValue;

    /**
     * @var boolean $searchable
     */
    protected $searchable;

    /**
    * @var boolean $localizable
    */
    protected $localizable;

    /**
     * @var boolean $scopable
     */
    protected $scopable;

    /**
     * @var array $properties
     */
    protected $properties;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return AbstractAttribute
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return AbstractAttribute
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return AbstractAttribute
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set entity type
     *
     * @param string $entityType
     *
     * @return AbstractAttribute
     */
    public function setEntityType($entityType)
    {
        $this->entityType = $entityType;

        return $this;
    }

    /**
     * Get entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Get created datetime
     *
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created datetime
     *
     * @param datetime $created
     *
     * @return TimestampableInterface
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get updated datetime
     *
     * @return datetime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated datetime
     *
     * @param datetime $updated
     *
     * @return TimestampableInterface
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Set backend storage
     *
     * @param string $storage
     *
     * @return AbstractAttribute
     */
    public function setBackendStorage($storage)
    {
        $this->backendStorage = $storage;

        return $this;
    }

    /**
     * Get backend storage
     *
     * @return string
     */
    public function getBackendStorage()
    {
        return $this->backendStorage;
    }

    /**
     * Set backend type
     *
     * @param string $type
     *
     * @return AbstractAttribute
     */
    public function setBackendType($type)
    {
        $this->backendType = $type;

        return $this;
    }

    /**
     * Get backend type
     *
     * @return string
     */
    public function getBackendType()
    {
        return $this->backendType;
    }

    /**
     * Set attribute type
     *
     * @param string $type
     *
     * @return AbstractAttribute
     */
    public function setAttributeType($type)
    {
        $this->attributeType = $type;

        return $this;
    }

    /**
     * Get frontend type
     *
     * @return string
     */
    public function getAttributeType()
    {
        return $this->attributeType;
    }

    /**
     * Set required
     *
     * @param boolean $required
     *
     * @return AbstractAttribute
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Is required
     *
     * @return boolean $required
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Set unique
     *
     * @param boolean $unique
     *
     * @return AbstractAttribute
     */
    public function setUnique($unique)
    {
        $this->unique = $unique;

        return $this;
    }

    /**
     * Is unique
     *
     * @return boolean $unique
     */
    public function isUnique()
    {
        return $this->unique;
    }

    /**
     * Set default value
     *
     * @param string $default
     *
     * @return AbstractAttribute
     */
    public function setDefaultValue($default)
    {
        $this->defaultValue = $default;

        return $this;
    }

    /**
     * Get default value
     *
     * @return string $unique
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set searchable
     *
     * @param boolean $searchable
     *
     * @return AbstractAttribute
     */
    public function setSearchable($searchable)
    {
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Is searchable
     *
     * @return boolean $searchable
     */
    public function isSearchable()
    {
        return $this->searchable;
    }

    /**
     * Set localizable
     *
     * @param boolean $localizable
     *
     * @return AbstractAttribute
     */
    public function setLocalizable($localizable)
    {
        $this->localizable = $localizable;

        return $this;
    }

    /**
     * Is localizable
     *
     * @return boolean $localizable
     */
    public function isLocalizable()
    {
        return $this->localizable;
    }

    /**
     * Set scopable
     *
     * @param boolean $scopable
     *
     * @return AbstractAttribute
     */
    public function setScopable($scopable)
    {
        $this->scopable = $scopable;

        return $this;
    }

    /**
     * Is scopable
     *
     * @return boolean $scopable
     */
    public function isScopable()
    {
        return $this->scopable;
    }

    /**
     * Add option
     *
     * @param AbstractAttributeOption $option
     *
     * @return AbstractAttribute
     */
    public function addOption(AbstractAttributeOption $option)
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * Remove option
     *
     * @param AbstractAttributeOption $option
     *
     * @return AbstractAttribute
     */
    public function removeOption(AbstractAttributeOption $option)
    {
        $this->options->removeElement($option);

        return $this;
    }

    /**
     * Get options
     *
     * @return \ArrayAccess
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get properties
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Set properties
     *
     * @param array $properties
     *
     * @return AbstractAttribute
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * Get a property
     *
     * @param string $property
     *
     * @return mixed
     */
    public function getProperty($property)
    {
        return isset($this->properties[$property]) ? $this->properties[$property] : null;
    }

    /**
     * Set a property
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return AbstractAttribute
     */
    public function setProperty($property, $value)
    {
        $this->properties[$property] = $value;

        return $this;
    }
}
