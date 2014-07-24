<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\ProductValue;

use Pim\Bundle\CatalogBundle\Model\Metric;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class MetricDenormalizer extends AbstractValueDenormalizer
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $value = $context['value'];

        if (null === $metric = $value->getMetric()) {
            $metric = new Metric();
            $metric->setData($data);
            $metric->setFamily($value->getAttribute()->getMetricFamily());
        } else {
            $metric->setUnit($data);
        }

        return $metric;
    }
}
