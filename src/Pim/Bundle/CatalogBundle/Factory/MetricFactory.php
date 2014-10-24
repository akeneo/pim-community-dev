<?php

namespace Pim\Bundle\CatalogBundle\Factory;

/**
 * Metric factory
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricFactory
{
    /** @var string */
    protected $metricClass;

    /**
     * @param string $metricClass
     */
    public function __construct($metricClass)
    {
        $this->metricClass = $metricClass;
    }

    /**
     * Create and configure a metric instance
     *
     * @param string $family
     *
     * @return \Pim\Bundle\CatalogBundle\Model\MetricInterface
     */
    public function createMetric($family)
    {
        $metric = new $this->metricClass();
        $metric->setFamily($family);

        return $metric;
    }
}
