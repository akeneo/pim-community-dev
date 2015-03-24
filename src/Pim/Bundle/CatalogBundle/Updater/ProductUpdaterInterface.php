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
interface ProductUpdaterInterface
{
    /**
     * Updates a product with associative array of field to data (erase the current data)
     *
     * @param ProductInterface $product The product to update
     * @param array            $data    The data to set
     * @param array            $options Options to pass to the setter
     *
     * @return ProductUpdaterInterface
     */
    public function update(ProductInterface $product, array $data, array $options = []);

    /**
     * Sets a data in a product field (erase the current data)
     *
     * @param ProductInterface $product The product to update
     * @param string           $field   The field to update
     * @param mixed            $data    The data to set
     * @param array            $options Options to pass to the setter
     *
     * @return ProductUpdaterInterface
     */
    public function setData(ProductInterface $product, $field, $data, array $options = []);

    /**
     * Adds a data in a product field (complete the current data)
     *
     * @param ProductInterface $product The product to update
     * @param string           $field   The field to update
     * @param mixed            $data    The data to add
     * @param array            $options Options to pass to the adder
     *
     * @return ProductUpdaterInterface
     */
    public function addData(ProductInterface $product, $field, $data, array $options = []);

    /**
     * Copy a data from a source field to a destination field (erase the current destination data)
     *
     * @param ProductInterface $fromProduct The product to read from
     * @param ProductInterface $toProduct   The product to update
     * @param string           $fromField   The field to read from
     * @param string           $toField     The field to update
     * @param array            $options     Options to pass to the copier
     *
     * @return ProductUpdaterInterface
     */
    public function copyData(
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        $fromField,
        $toField,
        array $options = []
    );

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
