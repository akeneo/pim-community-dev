<?php
namespace Akeneo\CatalogBundle\Model\MongoDB;

use Akeneo\CatalogBundle\Model\AbstractModel;
use Akeneo\CatalogBundle\Document\ProductMongo;

/**
 * Flexible product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Product extends AbstractModel
{

    // TODO: add param for entity FQCN or shortname

    /**
     * Load encapsuled entity
     * @param integer
     * @return ProductType
     */
    public function find($productId)
    {
        // get document
        $document = $this->manager->getRepository('AkeneoCatalogBundle:ProductMongo')
            ->find($productId);
        if ($document) {
            $this->object = $document;
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
        $this->object = new ProductMongo();
        $this->object->setType($type);
        // TODO deal with group
        return $this;
    }

    /**
     * Get product value for a field code
     *
     * @param string $fieldCode
     * @return mixed
     */
    public function getValue($fieldCode)
    {
        $value = $this->getObject()->getValue($fieldCode);
        return $value;
    }

    /**
     * Set product value for a field
     *
     * @param string $fieldCode
     * @param string $data
     */
    public function setValue($fieldCode, $data)
    {
        // TODO: check type
        $this->getObject()->setValue($fieldCode, $data);
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
     * get locale code
     *
     * @return string $locale
     */
    public function getLocale()
    {
        $this->getObject()->getLocale();
    }

    /**
     * Change locale and refresh data for this locale
     *
     * @param string $locale
     */
    public function switchLocale($locale)
    {
        $this->getObject()->setTranslatableLocale($locale);
    }

}