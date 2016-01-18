<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Sets a field in a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FieldSetterInterface extends SetterInterface
{
    /**
     * Set field data
     *
     * @param ProductInterface $product The product to modify
     * @param string           $field   The field of the product to modify
     * @param mixed            $data    The data to set
     * @param array            $options Options passed to the setter
     */
    public function setFieldData(ProductInterface $product, $field, $data, array $options = []);

    /**
     * Supports the field
     *
     * @param string $field
     *
     * @return bool
     */
    public function supportsField($field);
}
