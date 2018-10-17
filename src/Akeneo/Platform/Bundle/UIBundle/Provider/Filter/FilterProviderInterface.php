<?php

namespace Akeneo\Platform\Bundle\UIBundle\Provider\Filter;

/**
 * Filter provider interface
 * This interface describe items which will be registred in the ChainedFilterProvider.
 * The goal is to provide the list of filter for the given element (an attribute for example).
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterProviderInterface
{
    /**
     * Get the Filter for the given element
     *
     * @param mixed $element
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
