<?php

namespace Pim\Component\Catalog\Model;

/**
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ValuesContainerInterface
{
    /**
     * @return array
     */
    public function getRawValues();

    /**
     * @param array $rawValues
     *
     * @return ProductInterface
     */
    public function setRawValues(array $rawValues);

    /**
     * Get values
     *
     * @return ProductValueCollectionInterface
     */
    public function getValues();

    /**
     * Set values
     *
     * @param ProductValueCollectionInterface $values
     *
     * @return ProductInterface
     */
    public function setValues(ProductValueCollectionInterface $values);

    /**
     * Get value related to attribute code
     *
     * @param string $attributeCode
     * @param string $localeCode
     * @param string $scopeCode
     *
     * @return ProductValueInterface
     */
    public function getValue($attributeCode, $localeCode = null, $scopeCode = null);

    /**
     * Add value, override to deal with relation owner side
     *
     * @param ProductValueInterface $value
     *
     * @return ProductInterface
     */
    public function addValue(ProductValueInterface $value);

    /**
     * Remove value
     *
     * @param ProductValueInterface $value
     *
     * @return ProductInterface
     */
    public function removeValue(ProductValueInterface $value);

    /**
     * Get the attributes of the product
     *
     * @return AttributeInterface[] the attributes of the current product
     */
    public function getAttributes();

    /**
     * Get whether or not an attribute is part of a product
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function hasAttribute(AttributeInterface $attribute);

    /**
     * Get the list of used attribute codes from the indexed values
     *
     * @return array
     */
    public function getUsedAttributeCodes();
}
