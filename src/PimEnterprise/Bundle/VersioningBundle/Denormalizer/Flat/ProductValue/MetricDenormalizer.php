<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Metric flat denormalizer used for attribute types:
 * - pim_catalog_metric
 *
 * @author Romain Monceau <romain@akeneo.com>
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
        if ($data === null || $data === '') {
            return null;
        }

        $resolver = new OptionsResolver();
        $this->configContext($resolver);
        $context = $resolver->resolve($context);

        $value = $context['value'];

        if (null === $metric = $value->getMetric()) {
            $metric = $this->factory->createMetric($value->getAttribute()->getMetricFamily());
            $metric->setData($data);
        } else {
            $metric->setUnit($data);
        }

        return $metric;
    }

    /**
     * Define context requirements
     * @param OptionsResolverInterface $resolver
     */
    protected function configContext(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['value']);
    }
}
