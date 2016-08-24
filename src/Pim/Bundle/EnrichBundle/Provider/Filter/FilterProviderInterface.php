<?php

namespace Pim\Bundle\EnrichBundle\Provider\Filter;

/**
 * Filter provider interface
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterProviderInterface
{
    /**
     * Get the Filter for the given element
     *
     * @param mixed $element
     *
     * @throws RuntimeException If no filter is found for the given element
     *
     * @return string
     */
    public function getFilters($element);

    /**
     * Does the Filter provider support the element
     *
     * @param mixed $element
     *
     * @return bool
     */
    public function supports($element);
}
