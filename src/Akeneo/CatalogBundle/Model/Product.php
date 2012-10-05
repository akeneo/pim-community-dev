<?php
namespace Akeneo\CatalogBundle\Model;

use Akeneo\CatalogBundle\Entity\Entity;
use Akeneo\CatalogBundle\Entity\Type;
use Akeneo\CatalogBundle\Entity\Field;
use Akeneo\CatalogBundle\Entity\Value;

/**
 * Flexible product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Product extends AbstractModel
{

    /**
     * Get real entity
     * @var Entity
     */
    protected $_entity;

    /**
     * Constructor
     * @param string $code
     */
    public function __construct($manager, $type)
    {
        parent::__construct($manager);

        $this->_entity = new Entity();
        $this->_entity->setType($type);
    }

    /**
     * Get product value for a field
     *
     * @param string $fieldCode
     * @return mixed
     */
    public function getValue($fieldCode)
    {
        // TODO check type
        $field = $this->_manager->getRepository('AkeneoCatalogBundle:Field')
        ->findOneByCode($fieldCode);
        if (!$field) {
            throw new \Exception("The field {$fieldCode} doesn't exist");
        }
        $value = null;
        if ($this->_entity->getId()) {
            // check value exists
            // TODO: pb nothing if never persist
            $value = $this->_manager->getRepository('AkeneoCatalogBundle:Value')
                ->findOneBy(array('field' => $field, 'product' => $this->_entity));
        }
        return (!$value) ? null : $value->getData();
    }

    /**
     * Set product value for a field
     *
     * @param string $fieldCode
     * @param string $data
     * @param string $locale
     */
    public function setValue($fieldCode, $data, $locale = null)
    {
        // TODO check type
        $field = $this->_manager->getRepository('AkeneoCatalogBundle:Field')
            ->findOneByCode($fieldCode);
        if (!$field) {
            throw new \Exception("The field {$fieldCode} doesn't exist");
        }
        $value = null;
        if ($this->_entity->getId()) {
            // check value exists
            $value = $this->_manager->getRepository('AkeneoCatalogBundle:Value')
                ->findOneBy(array('field' => $field, 'product' => $this->_entity));
        }
        if (!$value) {
            $value = new Value();
            $value->setField($field);
            $value->setProduct($this->_entity);
            $this->_entity->addValue($value);
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
            case (0 === strpos($method, 'get')):
                $by = substr($method, 3);
                $method = 'getValue';
                break;
            case (0 === strpos($method, 'set')):
                $by = substr($method, 3);
                $method = 'setValue';
                break;
            default:
                throw new \BadMethodCallException(
                    "Undefined method '$method'. The method name must start with ".
                    "either get or set!"
                );
        }
        // get field code
        $fieldName = lcfirst(\Doctrine\Common\Util\Inflector::classify($by));
        // call method
        if ($this->hasField($fieldName)) {
            switch (count($arguments)) {
                case 0:
                    return $this->$method($fieldName);
                case 1:
                    return $this->$method($fieldName, $arguments[0]);
                default:
                    // do nothing
            }
        }
        throw new \Exception('Invalid getX / setX call');
    }

    /**
     * Persist type
     *
     * @return Product
     */
    public function persistAndFlush()
    {
        $this->_manager->persist($this->_entity);
        $this->_manager->flush();
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

}