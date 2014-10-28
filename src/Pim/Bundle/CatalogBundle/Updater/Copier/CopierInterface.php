<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Copy a value from a field to another in many products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CopierInterface
{
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

    /**
     * Supports the fields
     *
     * @param string $sourceField
     * @param string $destinationField
     *
     * @return true
     */
    public function supports($sourceField, $destinationField);
}
