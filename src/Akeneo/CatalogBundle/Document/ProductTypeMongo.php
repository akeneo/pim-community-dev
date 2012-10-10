<?php
namespace Akeneo\CatalogBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Product type as Mongo Document
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @MongoDB\Document
 */
class ProductTypeMongo
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $code;

    /**
     * TODO: groups to organize fields but with not strong constraint to keep light model relation
     * @MongoDB\Raw
     */
    protected $groups = array();

    /**
     * @MongoDB\ReferenceMany(targetDocument="ProductFieldMongo", cascade={"persist"})
     * @var ArrayCollection
     */
    protected $fields = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = array();
        $this->fields = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return ProductTypeMongo
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get code
     *
     * @return string $code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Add fields
     *
     * @param Akeneo\CatalogBundle\Document\ProductFieldMongo $fields
     * @param string $groupCode
     */
    public function addFieldToGroup(\Akeneo\CatalogBundle\Document\ProductFieldMongo $field, $groupCode)
    {
        $this->fields[] = $field;
        if (!isset($this->groups[$groupCode])) {
            $this->groups[$groupCode] = array();
        }
        $this->groups[$groupCode][] = $field->getCode();
    }

    /**
     * Get fields
     *
     * @return Doctrine\Common\Collections\Collection $fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Add fields
     *
     * @param string $groupCode
     */
    public function addGroup($groupCode)
    {
        if (!isset($this->groups[$groupCode])) {
            $this->groups[$groupCode] = array();
        }
    }

    /**
     * Set groups
     *
     * @param raw $groups
     * @return ProductTypeMongo
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * Get groups
     *
     * @return raw $groups
     */
    public function getGroups()
    {
        return $this->groups;
    }


    /**
     * Add fields
     *
     * @param Akeneo\CatalogBundle\Document\ProductFieldMongo $fields
     */
    public function addFields(\Akeneo\CatalogBundle\Document\ProductFieldMongo $fields)
    {
        $this->fields[] = $fields;
    }
}
