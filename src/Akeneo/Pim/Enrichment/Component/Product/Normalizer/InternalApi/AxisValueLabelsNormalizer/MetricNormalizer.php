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
    /** @var StandardMetricNormalizer|null */
    private $metricNormalizer;

    /** @var MetricLocalizer|null */
    private $metricLocalizer;

    /**
     * TODO: merge -> remove nullable and add BC break on UPGRADE
     */
    public function __construct(?StandardMetricNormalizer $metricNormalizer = null, ?MetricLocalizer $metricLocalizer = null)
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
        if ($this->metricLocalizer === null || $this->metricNormalizer === null) {
            return sprintf('%s %s', $value->getData(), $value->getUnit());
        }

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
