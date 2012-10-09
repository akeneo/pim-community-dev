<?php
namespace Akeneo\CatalogBundle\Model\MongoDB;

use Akeneo\CatalogBundle\Model\AbstractModel;
use Akeneo\CatalogBundle\Document\ProductMongo;
use Akeneo\CatalogBundle\Document\ProductTypeMongo;
use Akeneo\CatalogBundle\Document\ProductFieldMongo;

/**
 * The product type service, a builder which allows to embed complexity of
 * CRUD operation, of persistence and revisioning of the flexible entity type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductType extends AbstractModel
{

    /**
     * Get code
     * @return string code
     */
    public function getCode()
    {
        return $this->getObject()->getCode();
    }

    /**
     * Get groups code
     * @return Array
     */
    public function getGroupsCodes()
    {
        return array_keys($this->getObject()->getGroups());
    }

    /**
     * Load embedded entity type
     *
     * @param string $code
     * @return ProductType
     */
    public function find($code)
    {
        // get entity type
        $type = $this->getManager()->getRepository('AkeneoCatalogBundle:ProductTypeMongo')
            ->findOneByCode($code);
        if ($type) {
            $this->_object = $type;
        } else {
            throw new \Exception("There is no product type with code {$code}");
        }
        return $this;
    }

    /**
     * Create an embeded type entity
     * @param string $code
     * @return ProductType
     */
    public function create($code)
    {
        $type = $this->getManager()->getRepository('AkeneoCatalogBundle:ProductTypeMongo')
            ->findOneByCode($code);
        if ($type) {
            // TODO create custom exception
            throw new \Exception("There is already a product type with the code {$code}");
        } else {
            $this->_object = new ProductTypeMongo();
            $this->_object->setCode($code);
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
        $this->getObject()->addGroup($groupCode);
        return $this;
    }

    /**
     * Add a field to the type
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
        // create a new field
        if (!$field) {
            $field = new ProductFieldMongo();
            $field->setCode($fieldCode);
            $field->setType($fieldType);
            $field->setLabel('hard coded');
        }
        // add field to group
        $this->getObject()->addFieldToGroup($field, $groupCode);
        return $this;
    }

    /**
     * Get field by code
     *
     * @param string $fieldCode
     */
    public function getField($fieldCode)
    {
        $field = $this->getManager()->getRepository('AkeneoCatalogBundle:ProductFieldMongo')
            ->findOneByCode($fieldCode);
        return $field;
    }

    /**
     * Remove field
     *
     * @param $code
     */
    public function removeField($fieldCode)
    {
        // TODO: deal if not already persisted
        // TODO remove from group
        $field = $this->getField($fieldCode);
        $this->getManager()->remove($field);
    }

    /**
     * Create and return flexible product of current type
     *
     * @return Product
     */
    public function newProductInstance()
    {
        $product = new Product($this->getManager());
        $product->create($this->getObject());
        return $product;
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

}