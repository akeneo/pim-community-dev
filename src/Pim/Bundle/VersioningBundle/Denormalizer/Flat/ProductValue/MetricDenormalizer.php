<?php

namespace Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use Pim\Component\Catalog\Factory\MetricFactory;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
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

        preg_match($singleFieldPattern, $data, $matches);

        return $this->factory->createMetric(
            $value->getAttribute()->getMetricFamily(),
            isset($matches['unit']) ? $matches['unit'] : null,
            isset($matches['data']) ? $matches['data'] : null
        );
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
            ->setDefined(['entity', 'locale_code', 'product', 'scope_code', 'metric_unit']);
    }
}
