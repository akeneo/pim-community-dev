<?php

namespace Pim\Bundle\CatalogBundle\Validator;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Contains the state of the unique value for a product, due to EAV model we cannot ensure it via constraints on
 * database, we use this state to deal with bulk update and validation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValuesSet
{
    /** @var array allows to keep the state */
    protected $uniqueValues;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->uniqueValues = [];
    }

    /**
     * Reset the set
     */
    public function reset()
    {
        $this->uniqueValues = [];
    }

    /**
     * Return true if value has been added, else if value already exists inside the set
     *
     * @param ProductValueInterface $productValue
     *
     * @return bool
     */
    public function addValue(ProductValueInterface $productValue)
    {
        $product = $productValue->getProduct();
        $productIdentifier = $this->getProductIdentifier($product);
        $productValueData = $this->getValueData($productValue);
        $uniqueValueCode = $this->getUniqueValueCode($productValue);

        if (isset($this->uniqueValues[$uniqueValueCode][$productValueData])) {
            $storedIdentifier = $this->uniqueValues[$uniqueValueCode][$productValueData];
            if ($storedIdentifier !== $productIdentifier) {
                return false;
            }
        }

        if (!isset($this->uniqueValues[$uniqueValueCode])) {
            $this->uniqueValues[$uniqueValueCode] = [];
        }

        if (!isset($this->uniqueValues[$uniqueValueCode][$productValueData])) {
            $this->uniqueValues[$uniqueValueCode][$productValueData] = $productIdentifier;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getUniqueValues()
    {
        return $this->uniqueValues;
    }

    /**
     * spl_object_hash for new product and id when product exists
     *
     * @param ProductInterface $product
     *
     * @return string
     */
    protected function getProductIdentifier(ProductInterface $product)
    {
        $identifier = $product->getId() ? $product->getId() : spl_object_hash($product);

        return $identifier;
    }

    /**
     * @param ProductValueInterface $productValue
     *
     * @return string
     */
    protected function getUniqueValueCode(ProductValueInterface $productValue)
    {
        $attributeCode = $productValue->getAttribute()->getCode();
        $uniqueValueCode = $attributeCode;
        $uniqueValueCode .= (null !== $productValue->getLocale()) ? $productValue->getLocale() : '';
        $uniqueValueCode .= (null !== $productValue->getScope()) ? $productValue->getScope() : '';

        return $uniqueValueCode;
    }

    /**
     * @param ProductValueInterface $productValue
     *
     * @return string
     */
    protected function getValueData(ProductValueInterface $productValue)
    {
        $data = $productValue->getData();

        return ($data instanceof \DateTime) ? $data->format('Y-m-d') : (string) $data;
    }
}
