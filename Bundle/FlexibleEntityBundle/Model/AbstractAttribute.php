<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model;

use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TimestampableInterface;

/**
 * Abstract entity attribute, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
abstract class AbstractAttribute implements TimestampableInterface
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
    * @var boolean $required
    */
    protected $required;

    /**
     * @var boolean $searchable
     */
    protected $searchable;

    /**
    * @var boolean $translatable
    */
    protected $translatable;

    /**
     * @var boolean $scopable
     */
    protected $scopable;

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
     * Set title
     *
     * @param string $title
     *
     * @return AbstractAttribute
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
     * @return AbstractAttribute
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
     * @return AbstractAttribute
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
     * Set required
     *
     * @param string $required
     *
     * @return AbstractAttribute
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Get required
     *
     * @return string $Required
     */
    public function getRequired()
    {
        return $this->required;
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
     * @return AbstractAttribute
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
     * Get scopable
     *
     * @return boolean $scopable
     */
    public function getScopable()
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
     */
    public function removeOption(AbstractAttributeOption $option)
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
