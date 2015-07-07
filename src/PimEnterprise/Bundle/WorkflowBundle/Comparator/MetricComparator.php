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

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\Metric;

/**
 * Comparator which calculate change set for metrics
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 *
 * @see    PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
 */
class MetricComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison(ProductValueInterface $value)
    {
        return 'pim_catalog_metric' === $value->getAttribute()->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(ProductValueInterface $value, $submittedData)
    {
        // Submitted metric is invalid (data was read only for example)
        if (!isset($submittedData['metric']['data']) || !isset($submittedData['metric']['unit'])) {
            return;
        }

        if ($this->hasNotChanged($value->getMetric(), $submittedData['metric'])) {
            return;
        }

        return [
            'metric' => [
                'data' => $submittedData['metric']['data'],
                'unit' => $submittedData['metric']['unit'],
            ]
        ];
    }

    /**
     * Detects changes in a metric compared to submitted data
     *
     * @param mixed $metric
     * @param array $submittedMetric
     *
     * @return boolean
     */
    protected function hasNotChanged($metric, $submittedMetric)
    {
        // Current value has a metric and submitted metric does not change it
        if ($metric instanceof Metric &&
            $metric->getData() == $submittedMetric['data'] &&
            $metric->getUnit() == $submittedMetric['unit']
        ) {
            return true;
        }

        // Current value has a metric with empty data and submitted metric data does not change it
        if ($metric instanceof Metric && null === $metric->getData() && empty($submittedMetric['data'])) {
            return true;
        }

        // Current value has no metric and submitted metric data is empty
        if (null === $metric && empty($submittedMetric['data'])) {
            return true;
        }

        return false;
    }
}
