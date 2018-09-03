<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

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
     * @param EntityWithValuesInterface $fromEntityWithValues
     * @param EntityWithValuesInterface $toEntityWithValues
     * @param AttributeInterface        $fromAttribute
     * @param AttributeInterface        $toAttribute
     * @param array                     $options
     */
    public function copyAttributeData(
        EntityWithValuesInterface $fromEntityWithValues,
        EntityWithValuesInterface $toEntityWithValues,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        array $options = []
    );

    /**
     * Supports the source and destination attributes, and ensure both attributes
     * are of the same type.
     *
     * @param AttributeInterface                                       $fromAttribute
     * @param AttributeInterface $toAttribute
     *
     * @return bool
     */
    public function supportsAttributes(AttributeInterface $fromAttribute, AttributeInterface $toAttribute);
}
