<?php

namespace Pim\Component\Catalog\Updater\Adder;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;

/**
 * Adds a data into a product's attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeAdderInterface extends AdderInterface
{
    /**
     * Add attribute data
     *
     * @param EntityWithValuesInterface $entityWithValues
     * @param AttributeInterface        $attribute The attribute of the product to update
     * @param mixed                     $data      The data to add
     * @param array                     $options   Options passed to the adder
     *
     * @return
     * @internal param ProductInterface $product The product to update
     */
    public function addAttributeData(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        $data,
        array $options = []
    );

    /**
     * Supports the attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function supportsAttribute(AttributeInterface $attribute);
}
