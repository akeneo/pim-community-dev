<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Provides basic operations to update a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductUpdaterInterface extends UpdaterInterface
{
    /**
     * Updates a product with associative array of field to data (erase the current data)
     *
     * @param object $product The product to update
     * @param array  $data    The data to set
     * @param array  $options Options to pass to the setter
     *
     * @return ProductUpdaterInterface
     */
    public function update($product, array $data, array $options = []);

    /**
     * Sets the data in values of many products
     *
     * @param ProductInterface[] $products
     * @param string             $field
     * @param mixed              $data
     * @param string             $locale
     * @param string             $scope
     *
     * @return ProductUpdaterInterface
     *
     * @deprecated will be removed in 1.5, please use setData(
     */
    public function setValue(array $products, $field, $data, $locale = null, $scope = null);

    /**
     * Copies a value from a source field to a destination field in many products
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
     *
     * @deprecated will be removed in 1.5, please use copyData(
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
}
