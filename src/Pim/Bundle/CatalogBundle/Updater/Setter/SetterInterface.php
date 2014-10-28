<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Sets a value in many products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SetterInterface
{
    /**
     * Set the value in products
     *
     * @param ProductInterface[] $products
     * @param string             $field
     * @param mixed              $data
     * @param array              $context
     */
    public function setValue(array $products, $field, $data, array $context = []);

    /**
     * Supports the field
     *
     * @return true
     */
    public function supports($field);
}
