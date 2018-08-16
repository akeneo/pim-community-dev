<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query\Sorter;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Sorter interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeSorterInterface extends SorterInterface
{
    /**
     * Sort by attribute value
     *
     * @param AttributeInterface $attribute the attribute to sort on
     * @param string             $direction the direction to use
     * @param string             $locale    the locale
     * @param string             $channel   the channel
     *
     * @return AttributeSorterInterface
     */
    public function addAttributeSorter(AttributeInterface $attribute, $direction, $locale = null, $channel = null);

    /**
     * This filter supports the attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function supportsAttribute(AttributeInterface $attribute);
}
