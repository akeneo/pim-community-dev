<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;

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
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     * @param string             $fromLocale
     * @param string             $toLocale
     * @param string             $fromScope
     * @param string             $toScope
     *
     * @throws \Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException
     * @throws \RuntimeException
     */
    public function copyValue(
        array $products,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale = null,
        $toLocale = null,
        $fromScope = null,
        $toScope = null
    );

    /**
     * Supports the source and destination attributes
     *
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     *
     * @return boolean
     */
    public function supports(AttributeInterface $fromAttribute, AttributeInterface $toAttribute);
}
