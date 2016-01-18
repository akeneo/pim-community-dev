<?php

namespace Pim\Component\Catalog\Updater\Adder;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

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
