<?php
namespace Akeneo\CatalogBundle\Entity;

use Akeneo\CatalogBundle\Doctrine\EntityManager;
use Akeneo\CatalogBundle\Entity\ProductEntity as EntityProductEntity;
use Akeneo\CatalogBundle\Entity\ProductType as EntityProductType;
use Akeneo\CatalogBundle\Entity\ProductGroup as EntityProductGroup;
use Akeneo\CatalogBundle\Entity\ProductField as EntityProductField;
use Akeneo\CatalogBundle\Entity\ProductValue as EntityProductValue;

/**
 * Manager of flexible product stored with doctrine entities
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductManager extends EntityManager
{

    // TODO: add param for entity FQCN

    /**
     * List of fields codes
     * @var Array
     */
    protected $codeToField;

    /**
     * List of fields codes
     * @var Array
     */
    protected $fieldCodeToValue;

    // TODO:
    // - define default locale
    // - fallback on translation on default (no by default -> if yes pb with reporting to know the untranslated ?)
    // - store default locale tranlsation in translation table (no by default)

    /**
     * Load encapsuled entity
     * @param integer
     * @return ProductType
     */
    public function find($productId)
    {
        // locale
        // TODO how to load ?
        /*if ($locale) {
            $this->locale = $locale;
        }*/
        // get entity
        $entity = $this->manager->getRepository('AkeneoCatalogBundle:ProductEntity')
            ->find($productId);
        if ($entity) {
            $this->object = $entity;
            // retrieve group code
            // TODO: move to product entity or custom repository
            // TODO : problem when change type referenced by entity
            $this->codeToField = array();
            $this->fieldCodeToValue = array();
            foreach ($this->object->getType()->getGroups() as $group) {
                $this->_codeToGroup[$group->getCode()]= $group;
                foreach ($group->getFields() as $field) {
                    $this->codeToField[$field->getCode()]= $field;
                }
            }
            foreach ($this->object->getValues() as $value) {
                $this->fieldCodeToValue[$value->getField()->getCode()]= $value;
            }
        } else {
            throw new \Exception("There is no product with id {$productId}");
        }
        return $this;
    }

    /**
     * Create an embeded type entity
     * @param string $type
     * @return Product
     */
    public function create($type)
    {
        $this->object = new EntityProductEntity();
        $this->object->setType($type);
        $this->codeToField = array();
        $this->fieldCodeToValue = array();
        // TODO: move to product entity or custom repository
        foreach ($type->getGroups() as $group) {
            foreach ($group->getFields() as $field) {
                $this->codeToField[$field->getCode()]= $field;
            }
        }
        return $this;
    }

    /**
     * Get field by code
     *
     * @param string $fieldCode
     */
    public function getField($fieldCode)
    {
//         echo '<br />Get Field --> '. $fieldCode .'<br />code to field.. <br />';
//         var_dump($this->codeToField);
//         echo '<br />';
        // check in model
        if (isset($this->codeToField[$fieldCode])) {
            return $this->codeToField[$fieldCode];
        }
        return null;
    }

    /**
     * Get fields codes
     *
     * @return Array
     */
    public function getFieldsCodes()
    {
        return array_keys($this->codeToField);
    }

    /**
     * Get product value for a field code
     *
     * @param string $fieldCode
     * @return mixed
     */
    public function getValue($fieldCode)
    {
        $field = $this->getField($fieldCode);
        if (!$field) {
            throw new \Exception("The field {$fieldCode} doesn't exist for this product type");
        }
        $value = $this->fieldCodeToValue[$fieldCode];
        return ($value) ? $value->getData() : null;
    }

    /**
     * Set product value for a field
     *
     * @param string $fieldCode
     * @param string $data
     */
    public function setValue($fieldCode, $data)
    {
        $field = $this->getField($fieldCode);
        if (!$field) {
            throw new \Exception("The field {$fieldCode} doesn't exist for this product type");
        }
        // insert / update value
        $value = isset($this->fieldCodeToValue[$fieldCode])? $this->fieldCodeToValue[$fieldCode] : null;
        if (!$value) {
            $value = new EntityProductValue();
            $value->setField($field);
            $value->setProduct($this->getObject());
            $this->getObject()->addValue($value);
            $this->fieldCodeToValue[$fieldCode]= $value;
        }
        // for current product locale (else use default)
        if ($this->getLocale()) {
            $value->setTranslatableLocale($this->getLocale());
        }
        $value->setData($data);
        return $this;
    }

    /**
     * Adds support for magic getter / setter.
     *
     * @return array|object The found entity/entities.
     * @throws BadMethodCallException  If the method called is an invalid find* method
     *                                 or no find* method at all and therefore an invalid
     *                                 method call.
     */
    public function __call($method, $arguments)
    {
        // check if method is getField or setField
        switch (true) {
            // getValue(code)
            case (0 === strpos($method, 'get')):
                $by = substr($method, 3);
                $method = 'getValue';
                $fieldName = lcfirst(\Doctrine\Common\Util\Inflector::classify($by));
                return $this->$method($fieldName);
                break;
            // setValue(code, value)
            case (0 === strpos($method, 'set')):
                $by = substr($method, 3);
                $method = 'setValue';
                $fieldName = lcfirst(\Doctrine\Common\Util\Inflector::classify($by));
                return $this->$method($fieldName, $arguments[0]);
                break;
        }
    }

}