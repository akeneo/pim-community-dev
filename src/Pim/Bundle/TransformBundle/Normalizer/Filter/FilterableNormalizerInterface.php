<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Filter;

/**
 * Defines the interface of filterable normalizers.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterableNormalizerInterface
{
    /**
     * @param array $filters
     *
     * @return FilterableNormalizerInterface
     */
    public function setFilters(array $filters);
}
