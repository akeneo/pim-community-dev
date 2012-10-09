<?php
namespace Akeneo\CatalogBundle\Model\Doctrine;

use Akeneo\CatalogBundle\Model\AbstractModel;
use Akeneo\CatalogBundle\Entity\ProductEntity as EntityProductEntity;
use Akeneo\CatalogBundle\Entity\ProductType as EntityProductType;
use Akeneo\CatalogBundle\Entity\ProductGroup as EntityProductGroup;
use Akeneo\CatalogBundle\Entity\ProductField as EntityProductField;
use Akeneo\CatalogBundle\Entity\ProductValue as EntityProductValue;

/**
 * Flexible product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Product extends AbstractModel
{

    // TODO: add param for entity FQCN

    /**
     * List of fields codes
     * @var Array
     */
    protected $_codeToField;

    /**
     * List of fields codes
     * @var Array
     */
    protected $_fieldCodeToValue;

    /**
     * Current locale code
     * @var string
     */
    protected $_localeCode;

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
        /*if ($localeCode) {
            $this->_localeCode = $localeCode;
        }*/
        // get entity
        $entity = $this->_manager->getRepository('AkeneoCatalogBundle:ProductEntity')
            ->find($productId);
        if ($entity) {
            $this->_object = $entity;
            // retrieve group code
            // TODO: move to product entity or custom repository
            // TODO : problem when change type referenced by entity
            $this->_codeToField = array();
            $this->_fieldCodeToValue = array();
            foreach ($this->_object->getType()->getGroups() as $group) {
                $this->_codeToGroup[$group->getCode()]= $group;
                foreach ($group->getFields() as $field) {
                    $this->_codeToField[$field->getCode()]= $field;
                }
            }
            foreach ($this->_object->getValues() as $value) {
                $this->_fieldCodeToValue[$value->getField()->getCode()]= $value;
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
        $this->_object = new EntityProductEntity();
        $this->_object->setType($type);
        $this->_codeToField = array();
        $this->_fieldCodeToValue = array();
        // TODO: move to product entity or custom repository
        foreach ($this->_object->getType()->getGroups() as $group) {
            foreach ($group->getFields() as $field) {
                $this->_codeToField[$field->getCode()]= $field;
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
        // check in model
        if (isset($this->_codeToField[$fieldCode])) {
            return $this->_codeToField[$fieldCode];
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
        return array_keys($this->_codeToField);
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
        $value = $this->_fieldCodeToValue[$fieldCode];
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
        $value = isset($this->_fieldCodeToValue[$fieldCode])? $this->_fieldCodeToValue[$fieldCode] : null;
        if (!$value) {
            $value = new EntityProductValue();
            $value->setField($field);
            $value->setProduct($this->getObject());
            $this->getObject()->addValue($value);
            $this->_fieldCodeToValue[$fieldCode]= $value;
        }
        // for current product locale (else use default)
        if ($this->_localeCode) {
            $value->setTranslatableLocale($this->_localeCode);
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

    /**
     * Change locale and refresh product data for this locale
     *
     * @param string $locale
     */
    public function switchLocale($locale)
    {
        $this->_localeCode = $locale;
        foreach ($this->getObject()->getValues() as $value) {
            $value->setTranslatableLocale($locale);
            $this->getManager()->refresh($value);
        }
    }

}