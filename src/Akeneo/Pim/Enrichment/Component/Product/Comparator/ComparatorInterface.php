<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Comparator;

/**
 * Compare and get changes between a supported value and its relative updated data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ComparatorInterface
{
    /**
     * Whether or not the class supports comparison
     *
     * @param string $data
     *
     * @return bool
     */
    public function supports($data);

    /**
     * Get the changes between a normalized product value instance and the updated data
     * If no changes detected, then the method returns null
     *
     * @param mixed $data
     * @param mixed $originals
     *
     * @return mixed
     */
    public function compare($data, $originals);
}
