<?php
namespace Oro\Bundle\DataModelBundle\Model\Entity;

use Oro\Bundle\DataModelBundle\Model\Attribute\Type\AbstractAttributeType;
use Oro\Bundle\DataModelBundle\Model\Behavior\TimestampableInterface;

/**
 * Abstract entity attribute, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
abstract class AbstractEntityAttribute implements TimestampableInterface
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $code
     */
    protected $code;

    /**
     * @var string $title
     */
    protected $title;

    /**
     * @var string $entityType
     */
    protected $entityType;

    /**
     * Kind of model to store values
     * @var string $backendModel
     */
    protected $backendModel;

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
     * @var boolean $uniqueValue
     */
    protected $uniqueValue;

    /**
    * @var boolean $valueRequired
    */
    protected $valueRequired;

    /**
     * @var boolean $searchable
     */
    protected $searchable;

    /**
    * @var boolean $translatable
    */
    protected $translatable;

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
     * @return AbstractEntityAttribute
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
     * @return AbstractEntityAttribute
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
     * Set entity type
     *
     * @param string $entityType
     *
     * @return AbstractEntityAttribute
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
     * Set title
     *
     * @param string $title
     *
     * @return AbstractEntityAttribute
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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
     * Set backend model
     *
     * @param string $model
     *
     * @return AbstractEntityAttribute
     */
    public function setBackendModel($model)
    {
        $this->backendModel = $model;

        return $this;
    }

    /**
     * Get backend model
     *
     * @return string
     */
    public function getBackendModel()
    {
        return $this->backendModel;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return AbstractEntityAttribute
     */
    public function setBackendType($type)
    {
        $this->backendType = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getBackendType()
    {
        return $this->backendType;
    }

    /**
     * Set uniqueValue
     *
     * @param boolean $uniqueValue
     *
     * @return AbstractEntityAttribute
     */
    public function setUniqueValue($uniqueValue)
    {
        $this->uniqueValue = $uniqueValue;

        return $this;
    }

    /**
     * Get uniqueValue
     *
     * @return boolean $uniqueValue
     */
    public function getUniqueValue()
    {
        return $this->uniqueValue;
    }

    /**
     * Set valueRequired
     *
     * @param string $valueRequired
     *
     * @return AbstractEntityAttribute
     */
    public function setValueRequired($valueRequired)
    {
        $this->valueRequired = $valueRequired;

        return $this;
    }

    /**
     * Get valueRequired
     *
     * @return string $valueRequired
     */
    public function getValueRequired()
    {
        return $this->valueRequired;
    }

    /**
     * Set searchable
     *
     * @param boolean $searchable
     *
     * @return AbstractEntityAttribute
     */
    public function setSearchable($searchable)
    {
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Get searchable
     *
     * @return boolean $searchable
     */
    public function getSearchable()
    {
        return $this->searchable;
    }

    /**
     * Set translatable
     *
     * @param boolean $translatable
     *
     * @return AbstractEntityAttribute
     */
    public function setTranslatable($translatable)
    {
        $this->translatable = $translatable;

        return $this;
    }

    /**
     * Get translatable
     *
     * @return boolean $translatable
     */
    public function getTranslatable()
    {
        return $this->translatable;
    }

    /**
     * Add option
     *
     * @param AbstractEntityAttributeOption $option
     *
     * @return AbstractEntityAttribute
     */
    public function addOption(AbstractEntityAttributeOption $option)
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * Remove option
     *
     * @param AbstractEntityAttributeOption $option
     */
    public function removeOption(AbstractEntityAttributeOption $option)
    {
        $this->options->removeElement($option);
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

}
