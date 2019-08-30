<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\MetricLocalizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\MetricNormalizer as StandardMetricNormalizer;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class MetricNormalizer implements AxisValueLabelsNormalizer
{
    /** @var StandardMetricNormalizer */
    private $metricNormalizer;

    /** @var MetricLocalizer */
    private $metricLocalizer;

    public function __construct(StandardMetricNormalizer $metricNormalizer, MetricLocalizer $metricLocalizer)
    {
        $this->metricNormalizer = $metricNormalizer;
        $this->metricLocalizer = $metricLocalizer;
    }

    /**
     * @param ValueInterface $value
     * @param string         $locale
     *
     * @return string
     */
    public function normalize(ValueInterface $value, string $locale): string
    {
        $context = ['locale' => $locale];

        $normalizedMetric = $this->metricNormalizer->normalize($value, 'standard', $context);

        $metric = [
            'amount' => $normalizedMetric['amount']->getData(),
            'unit' => $value->getUnit()
        ];

        $localizedMetric = $this->metricLocalizer->localize($metric, $context);

        return sprintf('%s %s', $localizedMetric['amount'], $localizedMetric['unit']);
    }

    public function supports(string $attributeType): bool
    {
        return AttributeTypes::METRIC === $attributeType;
    }
}
