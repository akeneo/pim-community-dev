<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;

/**
 * Product filter interface
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterInterface
{
    /**
     * Filter product's values to have only updated or new values
     *
     * @param EntityWithValuesInterface $entity
     * @param array                     $newValues
     *
     * @return array
     */
    public function filter(EntityWithValuesInterface $entity, array $newValues): array;
}
