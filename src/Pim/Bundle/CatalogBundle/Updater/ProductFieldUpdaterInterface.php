<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Provides basic operations to update a product field
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductFieldUpdaterInterface
{
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
     * Removes a data in a product field (only provided data will be removed)
     *
     * @param ProductInterface $product The product to update
     * @param string           $field   The field to update
     * @param mixed            $data    The data to remove
     * @param array            $options Options to pass to the remover
     *
     * @return ProductUpdaterInterface
     */
    public function removeData(ProductInterface $product, $field, $data, array $options = []);
}
