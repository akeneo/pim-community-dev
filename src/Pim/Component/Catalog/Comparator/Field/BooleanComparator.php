<?php

namespace Pim\Component\Catalog\Comparator\Field;

use Pim\Component\Catalog\Comparator\ComparatorInterface;

/**
 * Comparator which calculate change set for booleans
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($column)
    {
        return in_array($column, ['enabled']);
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals)
    {
        if (null !== $originals && (bool) $originals === (bool) $data) {
            return null;
        }

        return $data;
    }
}
