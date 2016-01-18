<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue;

use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Metric flat denormalizer used for attribute types:
 * - pim_catalog_metric
 *
 * @author    Romain Monceau <romain@akeneo.com>
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
        $data = ('' === $data) ? null : $data;

        $resolver = new OptionsResolver();
        $this->configContext($resolver);
        $context = $resolver->resolve($context);

        $value = $context['value'];
        $matches = [];
        $singleFieldPattern = '/(?P<data>\d+(.\d+)?) (?P<unit>\w+)/';

        if (preg_match($singleFieldPattern, $data, $matches) === 0) {
            $metric = $this->addFromManyFields($value, $data);
        } else {
            $metric = $this->addFromSingleField($value, $matches['data'], $matches['unit']);
        }

        return $metric;
    }

    /**
     * @param ProductValueInterface $value
     * @param string                $data
     * @param string                $unit
     *
     * @return \Pim\Bundle\CatalogBundle\Model\MetricInterface
     */
    protected function addFromSingleField(ProductValueInterface $value, $data, $unit)
    {
        if (null === $metric = $value->getMetric()) {
            $metric = $this->factory->createMetric($value->getAttribute()->getMetricFamily());
        }
        $metric->setData($data);
        $metric->setUnit($unit);

        return $metric;
    }

    /**
     * The metric is built by many ordered calls, one for the data column, one for the unit column
     *
     * @param ProductValueInterface $value
     * @param string                $dataOrUnit
     *
     * @return \Pim\Bundle\CatalogBundle\Model\MetricInterface
     */
    protected function addFromManyFields(ProductValueInterface $value, $dataOrUnit)
    {
        // TODO come from original implementation, really FRAGIL because depends on many ordered calls
        if (null === $metric = $value->getMetric()) {
            $metric = $this->factory->createMetric($value->getAttribute()->getMetricFamily());
            $metric->setData($dataOrUnit);
        } else {
            $metric->setUnit($dataOrUnit);
        }

        return $metric;
    }

    /**
     * Define context requirements
     *
     * @param OptionsResolver $resolver
     */
    protected function configContext(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['value'])
            ->setDefined(
                ['entity', 'locale_code', 'product', 'scope_code', 'metric_unit']
            );
    }
}
