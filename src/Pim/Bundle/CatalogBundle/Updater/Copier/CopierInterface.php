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
     * @param string             $fromField
     * @param string             $toField
     * @param string             $fromLocale
     * @param string             $toLocale
     * @param string             $fromScope
     * @param string             $toScope
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
     * Supports the fields
     *
     * @param string $fromField
     * @param string $toField
     *
     * @return true
     */
    public function supports($fromField, $toField);
}
