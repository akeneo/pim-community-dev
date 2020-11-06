<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

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
     * @return mixed
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getIdentifier();

    public function getRawValues(): array;

    /**
     * @param array $rawValues
     */
    public function setRawValues(array $rawValues): \Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;

    /**
     * Get values
     */
    public function getValues(): \Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;

    /**
     * Set values
     *
     * @param WriteValueCollection $values
     */
    public function setValues(WriteValueCollection $values): \Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;

    /**
     * Get value related to attribute code
     *
     * @param string $attributeCode
     * @param string $localeCode
     * @param string $scopeCode
     */
    public function getValue(string $attributeCode, string $localeCode = null, string $scopeCode = null): \Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

    /**
     * Add value, override to deal with relation owner side
     *
     * @param ValueInterface $value
     */
    public function addValue(ValueInterface $value): \Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;

    /**
     * Remove value
     *
     * @param ValueInterface $value
     */
    public function removeValue(ValueInterface $value): \Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;

    /**
     * Get whether or not an attribute is part of a product from its attribute code
     */
    public function hasAttribute(string $attributeCode): bool;

    /**
     * Get the list of used attribute codes from the indexed values
     */
    public function getUsedAttributeCodes(): array;
}
