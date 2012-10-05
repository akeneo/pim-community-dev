<?php
namespace Akeneo\CatalogBundle\Model;

use Akeneo\CatalogBundle\Entity\Entity;
use Akeneo\CatalogBundle\Entity\Type;
use Akeneo\CatalogBundle\Entity\Group;
use Akeneo\CatalogBundle\Entity\Field;

/**
 * Product type, a facade of bean
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductType extends AbstractModel
{

    /**
     * @var string $code
     */
    protected $_code;

    /**
     * Get real entity type
     * @var Type
     */
    protected $_entityType;

    /**
    * Constructor
    * @param string $code
    */
    public function __construct($manager, $code = null)
    {
        parent::__construct($manager);
        $this->_code = $code;

        // get entity type
        $entityType = $this->_manager->getRepository('AkeneoCatalogBundle:Field')
            ->findOneByCode($code);
        if ($entityType) {
            $this->_entityType = $entityType;
        } else {
            $this->_entityType = new Type();
            $this->_entityType->setCode($code);
        }
    }

    /**
     * Get code
     * @return string code
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Set unique code
     *
     * @param string code
     * @return EntityType
     */
    public function setCode($code)
    {
        $this->_code = $code;
        return $this;
    }

    /**
     * Add a field to product type
     *
     * @param string $fieldCode
     * @param string $fieldType
     * @param string $groupCode
     * @return ProductType
     */
    public function addField($fieldCode, $fieldType, $groupCode)
    {
        // check if field already exists
        $field = $this->getField($fieldCode);
        if ($field) {
            throw new \Exception("Field '{$fieldCode}' already exists");
        }
        // create a new field
        $field = new Field();
        $field->setCode($fieldCode);
        $field->setType($fieldType);
        // check if group already exists, else create a new one
        $group = $this->getGroup($groupCode);
        if (!$group) {
            $group = new Group();
            $group->setType($this->_entityType);
            $group->setCode($groupCode);
            $this->_entityType->addGroup($group);
        }
        // add field to group
        $group->addField($field);
        return $this;
    }

    /**
     * Field exists ?
     *
     * @param string $fieldCode
     * @return boolean
     */
    public function hasField($fieldCode)
    {
        return $this->getField($fieldCode) != null;
    }

    /**
     * TODO: move in repository
     * Get field by code
     *
     * @param string $fieldCode
     */
    public function getField($fieldCode)
    {
        $field = $this->_manager->getRepository('AkeneoCatalogBundle:Field')
            ->findOneByCode($fieldCode);
        return $field;
    }

    /**
     * TODO: move in repository
     * Get group by code
     *
     * @param string $fieldGroup
     */
    public function getGroup($groupCode)
    {
        $group = $this->_manager->getRepository('AkeneoCatalogBundle:Group')
            ->findOneBy(array('type' => $this->_entityType->getId(), 'code' => $groupCode));
        return $group;
    }

    /**
     * Create and return product
     *
     * @return Product
     */
    public function newProductInstance()
    {
        $product = new Product($this->_manager, $this->_entityType);
        return $product;
    }

    /**
     * Persist type
     *
     * @return Product
     */
    public function persistAndFlush()
    {
        $this->_manager->persist($this->_entityType);
        $this->_manager->flush();
        return $this;
    }

    /*
    -> update all entities ? revision ?
    -> proposal / idea :
    add base field : for every entity
    add “type” / “group” field : for every entity of a set (ex: tshirt) & add a setType or something like this
    addAttributeGroup($code)
    getAttributeGroup($code)
    removeAttributeGroup($code, forceIfNotEmpty = false)
    how to manage non-empty group removal : throws NonEmptyAttributeGroupException
    removeAttribute ($code)
    newFlexibleEntityInstance()
*/


}