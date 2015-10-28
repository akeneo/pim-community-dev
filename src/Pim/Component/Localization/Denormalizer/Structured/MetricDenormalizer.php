<?php

namespace Pim\Component\Localization\Denormalizer\Structured;

use Pim\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricDenormalizer implements DenormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['json'];

    /** @var DenormalizerInterface */
    protected $metricDenormalizer;

    /** @var LocalizerInterface */
    protected $localizer;

    /** @var string[] */
    protected $supportedTypes;

    /**
     * @param DenormalizerInterface $metricDenormalizer
     * @param LocalizerInterface    $localizer
     * @param string[]              $supportedTypes
     */
    public function __construct(
        DenormalizerInterface $metricDenormalizer,
        LocalizerInterface $localizer,
        array $supportedTypes
    ) {
        $this->metricDenormalizer = $metricDenormalizer;
        $this->localizer          = $localizer;
        $this->supportedTypes     = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $metric = $this->metricDenormalizer->denormalize($data, $class, $format, $context);

        if (null !== $metric) {
            $metric->setData($this->localizer->convertDefaultToLocalized($metric->getData(), $context));
        }

        return $metric;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($type, $this->supportedTypes) && in_array($format, $this->supportedFormats);
    }
}
