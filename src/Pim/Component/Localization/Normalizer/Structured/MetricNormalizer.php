<?php

namespace Pim\Component\Localization\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a metric with a localized format
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['json', 'xml'];

    /** @var NormalizerInterface */
    protected $metricNormalizer;

    /** @var LocalizerInterface */
    protected $localizer;

    /**
     * @param NormalizerInterface $metricNormalizer
     * @param LocalizerInterface  $localizer
     */
    public function __construct(NormalizerInterface $metricNormalizer, LocalizerInterface $localizer)
    {
        $this->metricNormalizer = $metricNormalizer;
        $this->localizer        = $localizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($metric, $format = null, array $context = [])
    {
        $metric = $this->metricNormalizer->normalize($metric, $format, $context);

        $metric['data'] = $this->localizer->localize($metric['data'], $context);

        return $metric;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MetricInterface && in_array($format, $this->supportedFormats);
    }
}
