<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueCollection;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Product template model, contains common product values as raw data
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplate implements ProductTemplateInterface
{
    /** @var int $id */
    protected $id;

    /** @var ProductValueCollectionInterface */
    protected $values;

    /** @var array */
    protected $valuesData = [];

    /**
     * Creates a new product template with empty values by default.
     */
    public function __construct()
    {
        $this->values = new ProductValueCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function setValues(ProductValueCollectionInterface $values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValuesData()
    {
        return $this->valuesData;
    }

    /**
     * {@inheritdoc}
     */
    public function setValuesData(array $valuesData)
    {
        $this->valuesData = $valuesData;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValue(ProductValueInterface $value)
    {
        $attributeCode = $value->getAttribute()->getCode();
        if (!isset($this->valuesData[$attributeCode])) {
            return false;
        }

        $valuesData = $this->valuesData[$attributeCode];
        foreach ($valuesData as $valueData) {
            if ($valueData['locale'] === $value->getLocale() && $valueData['scope'] === $value->getScope()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValueForAttribute(AttributeInterface $attribute)
    {
        return isset($this->valuesData[$attribute->getCode()]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasValueForAttributeCode($attributeCode)
    {
        return isset($this->valuesData[$attributeCode]);
    }

    /**
     * Get attributes of the product template
     *
     * @return array
     */
    public function getAttributeCodes()
    {
        return array_keys($this->valuesData);
    }
}
