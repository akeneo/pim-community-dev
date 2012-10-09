<?php
namespace Akeneo\CatalogBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;

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
    protected $values;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new ArrayCollection();
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
        return $this->values;
    }
}
