<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\ProductValue;

use Pim\Bundle\CatalogBundle\Factory\MetricFactory;

/**
 * Metric flat denormalizer used for attribute types:
 * - pim_catalog_metric
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class MetricDenormalizer extends AbstractValueDenormalizer
{
    /** @var MetricFactory */
    protected $factory;

    /**
     * @param array         $supportedTypes
     * @param MetricFactory $factory
     */
    public function __construct(array $supportedTypes, MetricFactory $factory)
    {
        parent::__construct($supportedTypes);

        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $value = $context['value'];

        if (null === $metric = $value->getMetric()) {
            $this->factory->createMetric($value->getAttribute()->getMetricFamily());
            $metric->setData($data);
        } else {
            $metric->setUnit($data);
        }

        return $metric;
    }
}
