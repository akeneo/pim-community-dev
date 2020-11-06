<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query\Filter;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Filter interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeFilterInterface extends FilterInterface
{
    /**
     * Add an attribute to filter
     *
     * @param AttributeInterface $attribute the attribute
     * @param string             $operator  the used operator
     * @param string|array       $value     the value(s) to filter
     * @param string             $locale    the locale
     * @param string             $channel   the channel
     * @param array              $options   the filter options
     */
    public function addAttributeFilter(
        AttributeInterface $attribute,
        string $operator,
        $value,
        string $locale = null,
        string $channel = null,
        array $options = []
    ): \Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;

    /**
     * This filter supports the attribute
     *
     * @param AttributeInterface $attribute
     */
    public function supportsAttribute(AttributeInterface $attribute): bool;

    /**
     * Returns supported attributes types
     *
     * @return string[]|array
     */
    public function getAttributeTypes(): array;
}
