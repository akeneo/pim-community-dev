<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a metric entity into an array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    protected $stdNormalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->stdNormalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($metric, $format = null, array $context = [])
    {
        $rawMetric = $this->stdNormalizer->normalize($metric, $format, $context);

        $rawMetric['base_data'] = $metric->getBaseData();
        $rawMetric['base_unit'] = $metric->getBaseUnit();
        $rawMetric['family'] = $metric->getFamily();

        return $rawMetric;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MetricInterface && 'storage' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
