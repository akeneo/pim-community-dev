<?php

namespace Pim\Component\Catalog\Updater\Copier;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Copies a data from a product's field to another product's field
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FieldCopierInterface extends CopierInterface
{
    /**
     * Copy a data from a source field to a destination field
     *
     * @param ProductInterface $fromProduct
     * @param ProductInterface $toProduct
     * @param string           $fromField
     * @param string           $toField
     * @param array            $options
     *
     * @throws \Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException
     * @throws \RuntimeException
     */
    public function copyFieldData(
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        $fromField,
        $toField,
        array $options = []
    );

    /**
     * Supports the source and destination fields
     *
     * @param string $fromField
     * @param string $toField
     *
     * @return bool
     */
    public function supportsFields($fromField, $toField);
}
