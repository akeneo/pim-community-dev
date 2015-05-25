<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Comparator;

/**
 * Comparator which calculate change set for metrics
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class MetricComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison($type)
    {
        return 'pim_catalog_metric' === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(array $data, array $originals)
    {
        if (!array_key_exists('value', $originals)) {
            return $data;
        }

        $diff = array_diff_assoc($data['value'], $originals['value']);

        if (!empty($diff)) {
            return $data;
        }

        return null;
    }
}
