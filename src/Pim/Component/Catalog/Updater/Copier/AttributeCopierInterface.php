<?php

namespace Pim\Component\Catalog\Updater\Copier;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

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
     * @throws \Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException
     * @throws \RuntimeException
     */
    public function copyAttributeData(
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        array $options = []
    );

    /**
     * Supports the source and destination attributes
     *
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     *
     * @return bool
     */
    public function supportsAttributes(AttributeInterface $fromAttribute, AttributeInterface $toAttribute);
}
