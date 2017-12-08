<?php

namespace Pim\Component\Catalog\Updater\Adder;

use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Adds a data into a product's field
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FieldAdderInterface extends AdderInterface
{
    /**
     * Set field data
     *
     * @param ProductInterface $product The product to modify
     * @param string           $field   The field of the product to modify
     * @param mixed            $data    The data to add
     * @param array            $options Options passed to the adder
     *
     * @throws PropertyException
     */
    public function addFieldData($product, $field, $data, array $options = []);

    /**
     * Supports the field
     *
     * @param string $field
     *
     * @return bool
     */
    public function supportsField($field);
}
