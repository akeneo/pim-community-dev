<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query\Sorter;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Aims to register sorters usable on product query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SorterRegistryInterface
{
    /**
     * Register the sorter
     *
     * @param SorterInterface $sorter
     */
    public function register(SorterInterface $sorter);

    /**
     * Get the field sorter
     *
     * @param string $field the field
     *
     * @return SorterInterface|null
     */
    public function getFieldSorter($field);

    /**
     * Get the attribute sorter
     *
     * @param AttributeInterface $attribute
     *
     * @return SorterInterface|null
     */
    public function getAttributeSorter(AttributeInterface $attribute);
}
