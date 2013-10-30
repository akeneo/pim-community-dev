<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Extra interface for attribute property transformers which need specific treatment
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductValueUpdaterInterface
{
    /**
     * Updates the ProductValue instance
     *
     * @param ProductValueInterface $productValue
     * @param mixed                 $data
     * @param array                 $options
     */
    public function updateProductValue(ProductValueInterface $productValue, $data, array $options = array());
}
