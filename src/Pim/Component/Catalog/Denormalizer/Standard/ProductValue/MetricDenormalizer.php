<?php

namespace Pim\Component\Catalog\Denormalizer\Standard\ProductValue;

use Pim\Component\Catalog\Factory\MetricFactory;

/**
 * Metric denormalizer used for attribute types:
 * - pim_catalog_metric
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (empty($data)) {
            return null;
        }

        $metric = $this->factory->createMetric(
            $context['attribute']->getMetricFamily(),
            $data['unit'],
            $data['amount']
        );

        return $metric;
    }
}
