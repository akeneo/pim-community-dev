<?php

namespace Pim\Component\Catalog\Updater\Remover;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Remove a value from a product
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeRemoverInterface extends RemoverInterface
{
    /**
     * Remove attribute data
     *
     * @param ProductInterface   $product   The product to modify
     * @param AttributeInterface $attribute The attribute of the product to modify
     * @param mixed              $data      The data to remove
     * @param array              $options   Options passed to the remover
     */
    public function removeAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    );

    /**
     * Supports the attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function supportsAttribute(AttributeInterface $attribute);
}
