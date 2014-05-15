<?php

namespace Pim\Bundle\TransformBundle\Filter;

/**
 * Defines the interface of filterable normalizers.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterableInterface
{
    /**
     * @param array $filters
     *
     * @return $this
     */
    public function setFilters(array $filters);
} 
