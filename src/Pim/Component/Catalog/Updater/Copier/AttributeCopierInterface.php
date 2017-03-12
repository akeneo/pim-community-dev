<?php

namespace Pim\Component\Catalog\Updater\Copier;

use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Copies a data from a product's attribute to another product's attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeCopierInterface extends CopierInterface
{
    /**
     * Copy a data from a source attribute to a destination attribute
     *
     * @param ProductInterface   $fromProduct
     * @param ProductInterface   $toProduct
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     * @param array              $options
     *
     * @throws PropertyException
     */
    public function copyAttributeData(
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        array $options = []
    );

    /**
     * Supports the source and destination attributes, and ensure both attributes
     * are of the same type.
     *
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     *
     * @return bool
     */
    public function supportsAttributes(AttributeInterface $fromAttribute, AttributeInterface $toAttribute);
}
