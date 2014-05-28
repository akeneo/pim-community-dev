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
class MetricComparator implements ComparatorInterface
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
    public function getChanges(AbstractProductValue $value, $submittedData)
    {
        $metric = $value->getMetric() ?: new Metric();
        if ($metric->getData() != $submittedData['metric']['data']
            || $metric->getUnit() != $submittedData['metric']['unit']
        ) {
            if (null === $metric->getData() && empty($submittedData['metric']['data'])) {
                return;
            }

            return [
                'metric' => [
                    'data' => $submittedData['metric']['data'],
                    'unit' => $submittedData['metric']['unit'],
                ]
            ];
        }
    }
}
