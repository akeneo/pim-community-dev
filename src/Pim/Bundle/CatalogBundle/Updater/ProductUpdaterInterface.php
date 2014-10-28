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
     * @param array              $context
     *
     * @return ProductUpdaterInterface
     */
    public function setValue(array $products, $field, $data, array $context = []);

    /**
     * Copy a value from a source field to a destination field in many products
     *
     * @param ProductInterface[] $products
     * @param string             $sourceField
     * @param string             $destinationField
     * @param array              $context
     *
     * @return ProductUpdaterInterface
     */
    public function copyValue(array $products, $sourceField, $destinationField, array $context = []);
}
