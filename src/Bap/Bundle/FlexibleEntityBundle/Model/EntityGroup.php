<?php
namespace Bap\Bundle\FlexibleEntityBundle\Model;

/**
 * Abstract entity group, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class EntityGroup
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
     * @var ArrayCollection $fields
     */
    protected $fields = array();

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
     * @return EntityGroup
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * Set code
     *
     * @param string $code
     * @return EntitySet
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return EntitySet
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
     * Add field
     *
     * @param EntityAttribute $fields
     * @return ProductGroup
     */
    public function addField(EntityAttribute $field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Remove field
     *
     * @param EntityAttribute $field
     */
    public function removeField(EntityAttribute $field)
    {
        $this->fields->removeElement($field);
    }

    /**
     * Get fields
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get group
     *
     * @param string $code
     * @return EntityAttribute
     */
    public function getField($code)
    {
        foreach ($this->fields as $field) {
            if ($field->getCode() == $code) {
                return $field;
            }
        }
        return false;
    }

}