<?php

namespace Pim\Component\Localization\Normalizer\Flat;

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
    protected $supportedFormats = ['csv', 'flat'];

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

        if (!isset($context['field_name']) || !isset($metric[$context['field_name']])) {
            return $metric;
        }

        $formattedMetric = ['data' => $metric[$context['field_name']]];
        $localizedMetric = $this->localizer->convertDefaultToLocalized($formattedMetric, $context);
        $metric[$context['field_name']] = $localizedMetric['data'];

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
