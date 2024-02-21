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
     *
     * @return AdderRegistryInterface
     */
    public function register(AdderInterface $adder);

    /**
     * Get a adder compatible with the given property
     *
     * @param string $property
     *
     * @return AdderInterface
     */
    public function getAdder($property);

    /**
     * @param string $field
     *
     * @return FieldAdderInterface
     */
    public function getFieldAdder($field);

    /**
     * @param AttributeInterface $attribute
     *
     * @return AttributeAdderInterface
     */
    public function getAttributeAdder(AttributeInterface $attribute);
}
