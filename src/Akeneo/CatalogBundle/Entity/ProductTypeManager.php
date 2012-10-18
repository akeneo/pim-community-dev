<?php
namespace Akeneo\CatalogBundle\Entity;

use Akeneo\CatalogBundle\Doctrine\EntityTypeManager;

/**
 * Manager of flexible product type stored with doctrine entities
 *
 * The product type service, a builder which allows to embed complexity of
 * CRUD operation, of persistence and revisioning of the flexible entity type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTypeManager extends EntityTypeManager
{

    /**
     * List of groups codes
     * @var Array
     */
    protected $_codeToGroup;

    /**
     * List of fields codes
     * @var Array
     */
    protected $_codeToField;

    /**
     * Get groups code
     * @return Array
     */
    public function getGroupsCodes()
    {
        return array_keys($this->_codeToGroup);
    }

    /**
     * Get fields code
     * @return Array
     */
    public function getFieldsCodes()
    {
        return array_keys($this->_codeToField);
    }

    /**
     * Load embedded entity type
     *
     * @param string $code
     * @return ProductType
     */
    public function find($code)
    {
        $type = parent::find($code);
        // if found prepare group and field array too
        if ($type) {
            $this->_codeToGroup = array();
            $this->_codeToField = array();
            // retrieve group code
            // TODO: move to type entity or custom repository
            foreach ($this->object->getGroups() as $group) {
                $this->_codeToGroup[$group->getCode()]= $group;
                // retrieve field code
                foreach ($group->getFields() as $field) {
                    $this->_codeToField[$field->getCode()]= $field;
                }
            }
        } else {
            return false;
        }
        return $this;
    }

    /**
     * Create an embeded type entity
     * @param string $code
     * @return ProductType
     */
    public function create($code, $title = null)
    {
        // check if exists
        $type = $this->repository->findOneByCode($code);
        if ($type) {
            // TODO create custom exception
            throw new \Exception("There is already an entity type {$this->class} with the code {$code}");
        } else {
            $this->object = new $this->class();
            $this->object->setCode($code);
            $this->_codeToGroup = array();
            $this->_codeToField = array();
        }
        return $this;
    }

    /**
     * Add a group to a product type
     *
     * @param string $groupCode
     * @return ProductType
     */
    public function addGroup($groupCode)
    {
        if (!isset($this->_codeToGroup[$groupCode])) {
            $group = new ProductGroup();
            $group->setType($this->getObject());
            $group->setCode($groupCode);
            $this->getObject()->addGroup($group);
            $this->_codeToGroup[$groupCode]= $group;
        }
        return $this;
    }

    /**
     * Get a group by code
     *
     * @param string $fieldGroup
     */
    public function getGroup($groupCode)
    {
        if (isset($this->_codeToGroup[$groupCode])) {
            return $this->_codeToGroup[$groupCode];
        }
    }

    /**
     * Remove group by code
     *
     * @param $code
     */
    public function removeGroup($groupCode)
    {
        // TODO how to manage non-empty group removal : throws NonEmptyAttributeGroupException
        $group = $this->getGroup($groupCode);
        $this->getObject()->removeGroup($group);
        unset($this->_codeToGroup[$groupCode]);
    }

    /**
     * Add a field to the type
     *
     * @param string $fieldCode
     * @param string $fieldType
     * @param string $groupCode
     * @return ProductType
     */
    public function addField($fieldCode, $fieldType, $groupCode, $title = null)
    {
        // check if field already exists
        $field = $this->getField($fieldCode);
        // create a new field
        if (!$field) {
            $field = new ProductField();
            $field->setCode($fieldCode);
            $field->setType($fieldType);
            if (!$title) {
                $title = $fieldCode;
            }
            $field->setTitle($title);
            $this->_codeToField[$fieldCode]= $field;
        }
        // check if group already exists, else create a new one
        $group = $this->getGroup($groupCode);
        if (!$group) {
            $this->addGroup($groupCode);
            $group = $this->getGroup($groupCode);
        }
        // add field to group
        $group->addField($field);
        return $this;
    }

    /**
     * Get field by code
     *
     * @param string $fieldCode
     */
    public function getField($fieldCode)
    {
        // check in model
        if (isset($this->_codeToField[$fieldCode])) {
            return $this->_codeToField[$fieldCode];
        // check in db
        } else {
            $field = $this->getManager()->getRepository('AkeneoCatalogBundle:ProductField')
                ->findOneByCode($fieldCode);
            return $field;
        }
    }

    /**
     * Remove field from group
     *
     * @param $code
     */
    public function removeFieldFromType($fieldCode)
    {
        $field = $this->getField($fieldCode);
        unset($this->_codeToField[$fieldCode]);

        // TODO remove from group -> products cascade ?
        //$this->getObject()->removeGroup($group);
        //unset($this->_codeToGroup[$groupCode]);
    }

    /**
     * Remove field
     *
     * @param $code
     */
    public function removeField($fieldCode)
    {
        $field = $this->getField($fieldCode);
        $this->getManager()->remove($field);
        unset($this->_codeToField[$fieldCode]);
    }

    /**
     * Refresh type state from database
     * @return ProductType
     */
    public function refresh()
    {
        // TODO : problem with groups and fields code arrays ?
        // TODO : deal with locale
        $this->getManager()->refresh($this->getObject());
        return $this;
    }

    /**
     * @see newEntityInstance
     * @return ProductManager
     */
    public function newProductInstance()
    {
        return parent::newEntityInstance();
    }

}