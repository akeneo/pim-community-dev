<?php

namespace Pim\Component\Catalog\Model;

/**
 * This interface wears the responsibility of having values.
 * A value is defined by an attribute, a locale, a scope and a data.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface EntityWithValuesInterface
{
    /**
     * @return array
     */
    public function getRawValues();

    /**
     * @param array $rawValues
     *
     * @return EntityWithValuesInterface
     */
    public function setRawValues(array $rawValues);

    /**
     * Get values
     *
     * @return ValueCollectionInterface
     */
    public function getValues();

    /**
     * Set values
     *
     * @param ValueCollectionInterface $values
     *
     * @return EntityWithValuesInterface
     */
    public function setValues(ValueCollectionInterface $values);

    /**
     * Get value related to attribute code
     *
     * @param string $attributeCode
     * @param string $localeCode
     * @param string $scopeCode
     *
     * @return ValueInterface
     */
    public function getValue($attributeCode, $localeCode = null, $scopeCode = null);

    /**
     * Add value, override to deal with relation owner side
     *
     * @param ValueInterface $value
     *
     * @return EntityWithValuesInterface
     */
    public function addValue(ValueInterface $value);

    /**
     * Remove value
     *
     * @param ValueInterface $value
     *
     * @return EntityWithValuesInterface
     */
    public function removeValue(ValueInterface $value);

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
