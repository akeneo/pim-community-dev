<?php
namespace Pim\Bundle\CatalogBundle\Document;

use Bap\Bundle\FlexibleEntityBundle\Doctrine\EntityManager;
use Pim\Bundle\CatalogBundle\Document\ProductEntity;

/**
 * Manager of flexible product stored with doctrine documents
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductManager extends EntityManager
{

    /**
     * Load encapsuled entity
     * @param integer
     * @return Product
     */
    public function find($productId)
    {
        // get document
        $document = $this->manager->getRepository('PimCatalogBundle:ProductEntity')
            ->find($productId);
        if ($document) {
            $this->object = $document;
        } else {
            throw new \Exception("There is no product with id {$productId}");
        }
        return $this;
    }

    /**
     * Load encapsuled entity by source id
     * @param string $sourceCode
     * @param string $sourceProductId
     * @return Product
     */
    public function findBySourceId($sourceCode, $sourceProductId)
    {
        // get document
        $fieldSourceId = $sourceCode.'_source_id';
        $fieldLocale   = 'values_'. $this->getLocale();
        
        // prepare query
        $document = $this->manager->createQueryBuilder('PimCatalogBundle:ProductEntity')
            ->field($fieldLocale .'.'. $fieldSourceId)->equals($sourceProductId)
            ->getQuery()
            ->getSingleResult();
        if ($document) {
            $this->object = $document;
        } else {
            return false;
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
        $this->object = new ProductEntity();
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
     * get locale code
     *
     * @return string $locale
     */
    public function getLocale()
    {
        if ($this->getObject()) {
                return $this->getObject()->getLocale();
        }
        return self::$defaultLocale;
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