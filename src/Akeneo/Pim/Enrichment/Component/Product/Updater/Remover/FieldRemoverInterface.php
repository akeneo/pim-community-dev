<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Remover;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;

/**
 * Remove a data from a product field
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FieldRemoverInterface extends RemoverInterface
{
    /**
     * Remove field data
     *
     * @param ProductInterface|ProductModelInterface $product The product to modify
     * @param string                                 $field   The field of the product to modify
     * @param mixed                                  $data    The data to remove
     * @param array                                  $options Options passed to the remover
     *
     * @throws PropertyException
     */
    public function removeFieldData($product, $field, $data, array $options = []);

    /**
     * Supports the field
     *
     * @param string $field
     *
     * @return bool
     */
    public function supportsField($field);
}
