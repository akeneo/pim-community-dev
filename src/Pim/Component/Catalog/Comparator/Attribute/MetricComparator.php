<?php

namespace Pim\Component\Catalog\Comparator\Attribute;

use Pim\Component\Catalog\Comparator\ComparatorInterface;

/**
 * Comparator which calculate change set for metrics
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return 'pim_catalog_metric' === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals)
    {
        $default = ['locale' => null, 'scope' => null, 'value' => []];
        $originals = array_merge($default, $originals);

        $diff = array_diff_assoc((array) $data['value'], (array) $originals['value']);

        if (!empty($diff)) {
            return $data;
        }

        return null;
    }
}
