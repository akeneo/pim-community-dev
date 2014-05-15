<?php

namespace Pim\Bundle\TransformBundle\Filter;

use Doctrine\Common\Collections\Collection;

/**
 * Defines the interface of filters.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterInterface
{
    /**
     * Filters an object into a set of arrays/scalars
     *
     * @param Collection $objects
     * @param array      $context
     *
     * @return Collection
     */
    public function filter(Collection $objects, array $context = []);
} 
