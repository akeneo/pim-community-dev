<?php

namespace spec\Pim\Bundle\CatalogBundle\Factory;

use PhpSpec\ObjectBehavior;

class MetricFactorySpec extends ObjectBehavior
{
    const METRIC_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Metric';

    function let()
    {
        $this->beConstructedWith(self::METRIC_CLASS);
    }

    function it_creates_a_metric()
    {
        $this->createMetric('foo')->shouldReturnAnInstanceOf(self::METRIC_CLASS);
    }
}
