<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Registry of setters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SetterRegistryInterface
{
    /**
     * Register a setter
     *
     * @param SetterInterface $setter
     */
    public function register(SetterInterface $setter): \Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterRegistryInterface;

    /**
     * Get a setter compatible with the given property
     *
     * @param string $property
     */
    public function getSetter(string $property): \Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterInterface;

    /**
     * Get the field setter
     *
     * @param string $field the field
     */
    public function getFieldSetter(string $field): ?FieldSetterInterface;

    /**
     * Get the attribute setter
     *
     * @param AttributeInterface $attribute
     */
    public function getAttributeSetter(AttributeInterface $attribute): ?AttributeSetterInterface;
}
