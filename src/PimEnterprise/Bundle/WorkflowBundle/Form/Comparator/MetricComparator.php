<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\Metric;

/**
 * Comparator which calculate change set for metrics
 *
 * @see PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class MetricComparator extends AbstractComparator
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison(AbstractProductValue $value)
    {
        return 'pim_catalog_metric' === $value->getAttribute()->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function getDataChanges(AbstractProductValue $value, $submittedData)
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
