<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Registry of adders
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AdderRegistryInterface
{
    /**
     * Register a adder
     *
     * @param AdderInterface $adder
     */
    public function register(AdderInterface $adder): \Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderRegistryInterface;

    /**
     * Get a adder compatible with the given property
     *
     * @param string $property
     */
    public function getAdder(string $property): \Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderInterface;

    /**
     * @param string $field
     */
    public function getFieldAdder(string $field): FieldAdderInterface;

    /**
     * @param AttributeInterface $attribute
     */
    public function getAttributeAdder(AttributeInterface $attribute): AttributeAdderInterface;
}
