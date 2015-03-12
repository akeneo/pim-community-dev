<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Update many products at a time
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductUpdaterInterface
{
    /**
     * Set the data in values of many products
     *
     * @param ProductInterface[] $products
     * @param string             $field
     * @param mixed              $data
     * @param string             $locale
     * @param string             $scope
     *
     * @return ProductUpdaterInterface
     *
     * @deprecated will be removed in 1.5, please use set(
     */
    public function setValue(array $products, $field, $data, $locale = null, $scope = null);

    /**
     * Copy a value from a source field to a destination field in many products
     *
     * @param ProductInterface[] $products
     * @param string             $fromField
     * @param string             $toField
     * @param string             $fromLocale
     * @param string             $toLocale
     * @param string             $fromScope
     * @param string             $toScope
     *
     * @return ProductUpdaterInterface
     */
    public function copyValue(
        array $products,
        $fromField,
        $toField,
        $fromLocale = null,
        $toLocale = null,
        $fromScope = null,
        $toScope = null
    );

    /**
     * Sets a data in a product field (erase the current data)
     *
     * @param ProductInterface $product The product to modify
     * @param string           $field   The field to modify
     * @param mixed            $data    The data to set
     * @param array            $options Options to pass to the setter
     */
    public function set(ProductInterface $product, $field, $data, array $options = []);

    /**
     * Adds a data in a product field (complete the current data)
     *
     * @param ProductInterface $product The product to modify
     * @param string           $field   The field to complete
     * @param mixed            $data    The data to add
     * @param array            $options Options to pass to the setter
     */
    public function addData(ProductInterface $product, $field, $data, array $options = []);
}
