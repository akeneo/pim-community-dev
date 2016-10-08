<?php

namespace Pim\Component\Catalog\Denormalizer\Structured\ProductValue;

use Akeneo\Component\Localization\Localizer\LocalizerInterface;
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

    /** @var LocalizerInterface */
    protected $localizer;

    /**
     * @param array              $supportedTypes
     * @param MetricFactory      $factory
     * @param LocalizerInterface $localizer
     */
    public function __construct(array $supportedTypes, MetricFactory $factory, LocalizerInterface $localizer)
    {
        parent::__construct($supportedTypes);

        $this->factory = $factory;
        $this->localizer = $localizer;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (empty($data)) {
            return null;
        }

        $metric = $this->factory->createMetric($context['attribute']->getMetricFamily());
        $metric->setData($this->localizer->localize($data['amount'], $context));
        $metric->setUnit($data['unit']);

        return $metric;
    }
}
