<?php
namespace Bap\Bundle\FlexibleEntityBundle\Model;

/**
 * Abstract entity attribute, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class EntityAttribute
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
     * @var string $type
     */
    protected $type;

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
     * @var integer $scope
     */
    protected $scope;

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
     * @return EntityAttribute
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
     * @return EntityAttribute
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
     * Set title
     *
     * @param string $title
     * @return EntityAttribute
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
     * Set type
     *
     * @param string $type
     * @return EntityAttribute
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * Set uniqueValue
     *
     * @param boolean $uniqueValue
     * @return EntityAttribute
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
     * @return EntityAttribute
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
     * @return EntityAttribute
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
     * @return EntityAttribute
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
     * Set scope
     *
     * @param integer $scope
     * @return EntityAttribute
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * Get scope
     *
     * @return integer $scope
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Add option
     *
     * @param EntityAttributeOption $option
     * @return EntityAttribute
     */
    public function addOption(EntityAttributeOption $option)
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * Remove option
     *
     * @param EntityAttributeOption $option
     */
    public function removeOption(EntityAttributeOption $option)
    {
        $this->options->removeElement($option);
    }

    /**
     * Get options
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getOptions()
    {
        return $this->options;
    }

}