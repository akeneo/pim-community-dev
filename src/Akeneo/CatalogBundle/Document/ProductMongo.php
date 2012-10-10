<?php
namespace Akeneo\CatalogBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Product as Mongo Document
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @MongoDB\Document
 */
class ProductMongo
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * Simple reference, only store id TODO: test
     * @MongoDB\ReferenceOne(targetDocument="ProductTypeMongo", simple=true)
     */
    protected $type;

    /**
     * TODO: problem : how to deal with typing ? define custom repository ?
     * @MongoDB\Raw
     * @var ArrayCollection
     */
    protected $values = array();

    /**
     * @Gedmo\Timestampable(on="create")
     * @MongoDB\Date
     */
    private $created;

    /**
     * @Gedmo\Timestampable(on="update")
     * @MongoDB\Date
     */
    private $updated;


    protected $locale;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = array(); //new ArrayCollection();
        $this->locale = 'en_US'; // TODO: use default application locale or current gui locale
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
     * Set type
     *
     * @param Akeneo\CatalogBundle\Document\ProductTypeMongo $type
     * @return ProductMongo
     */
    public function setType(\Akeneo\CatalogBundle\Document\ProductTypeMongo $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return Akeneo\CatalogBundle\Document\ProductTypeMongo $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set values
     *
     * @param string $code
     * @param string $value
     * @return ProductMongo
     */
    public function setValue($code, $value)
    {
        if (!isset($this->values[$this->locale])) {
            $this->values[$this->locale] = array();
        }
        $this->values[$this->locale][$code] = $value;
        return $this;
    }

    /**
     * Set values
     *
     * @param raw $values
     * @return ProductMongo
     */
    public function addValue($value)
    {
        $this->values[] = $values;
        return $this;
    }

    /**
     * Set values
     *
     * @param raw $values
     * @return ProductMongo
     */
    public function setValues($values)
    {
        $this->values = $values;
        return $this;
    }

    /**
     * Get values
     *
     * @return raw $values
     */
    public function getValues()
    {
        return $this->values[$this->locale];
    }

    /**
     * Set created
     *
     * @param date $created
     * @return ProductMongo
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Get created
     *
     * @return date $created
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param date $updated
     * @return ProductMongo
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * Get updated
     *
     * @return date $updated
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
    * Set used locale
    * @param string $locale
    */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}
