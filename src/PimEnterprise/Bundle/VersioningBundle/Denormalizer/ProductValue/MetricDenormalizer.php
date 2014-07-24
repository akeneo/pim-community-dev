<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\ProductValue;

use Pim\Bundle\CatalogBundle\Model\Metric;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class MetricDenormalizer implements DenormalizerInterface
{
    /** @var array */
    protected $supportedTypes = array('pim_catalog_metric');

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $value = $context['value'];

        if (null === $metric = $value->getMetric()) {
            $metric = new Metric();
            $metric->setData($data);
        } else {
            $metric->setUnit($data);
        }

        return $metric;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($type, $this->supportedTypes) && 'csv' === $format;
    }
}
