<?php
// src/Strixos/CatalogBundle/Document/Product.php
namespace Strixos\CatalogBundle\Document;

use Strixos\CoreBundle\Model\AbstractModel;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 *
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @MongoDB\Document
 */
class Product extends AbstractModel
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $sku;

    /**
    * @MongoDB\String
    */
    protected $attributeSetCode;

    /**
    * @MongoDB\Raw
    *
    * TODO: problem : we miss typing ? define custom repository ?
    */
    private $values = array();

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
     * Set sku
     *
     * @param string $sku
     * @return Product
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * Get sku
     *
     * @return string $sku
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Add value
     *
     * @param string $attributeCode
     * @param mixed $value
    */
    public function addValue($attributeCode, $value)
    {
        $this->values[$attributeCode]= $value;
    }

    /**
     * Get value
     *
     * @param string $attributeCode
     * @return mixed $value
     */
    public function getValue($attributeCode)
    {
        return (isset($this->values[$attributeCode]))? $this->values[$attributeCode] : null;
    }

    /**
     * Set values
     *
     * @param collection $values
     * @return Product
     */
    public function setValues($values)
    {
        $this->values = $values;
        return $this;
    }

    /**
     * Get values
     *
     * @return collection $values
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set attributeSetCode
     *
     * @param string $attributeSetCode
     * @return Product
     */
    public function setAttributeSetCode($attributeSetCode)
    {
        $this->attributeSetCode = $attributeSetCode;
        return $this;
    }

    /**
     * Get attributeSetCode
     *
     * @return string $attributeSetCode
     */
    public function getAttributeSetCode()
    {
        return $this->attributeSetCode;
    }

    // TODO : store set id in place of set code and add logic to retrieve related set entity

}
