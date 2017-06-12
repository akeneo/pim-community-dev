<?php

namespace Pim\Component\Catalog\Updater\Copier;

use Pim\Component\Catalog\Model\AttributeInterface;

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
     * @param ValuesContainerInterface $fromValuesContainer
     * @param ValuesContainerInterface $toValuesContainer
     * @param AttributeInterface                        $fromAttribute
     * @param AttributeInterface                        $toAttribute
     * @param array                                     $options
     *
     * @return
     */
    public function copyAttributeData(
        ValuesContainerInterface $fromValuesContainer,
        ValuesContainerInterface $toValuesContainer,
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
