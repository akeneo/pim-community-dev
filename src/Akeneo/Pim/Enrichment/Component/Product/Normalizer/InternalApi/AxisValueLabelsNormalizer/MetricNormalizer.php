<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\MetricLocalizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\MetricNormalizer as StandardMetricNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\GetUnitTranslations;
use Webmozart\Assert\Assert;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class MetricNormalizer implements AxisValueLabelsNormalizer
{
    private StandardMetricNormalizer $metricNormalizer;
    private MetricLocalizer $metricLocalizer;
    private GetUnitTranslations $getUnitTranslations;

    public function __construct(
        StandardMetricNormalizer $metricNormalizer,
        MetricLocalizer $metricLocalizer,
        GetUnitTranslations $getUnitTranslations
    ) {
        $this->metricNormalizer = $metricNormalizer;
        $this->metricLocalizer = $metricLocalizer;
        $this->getUnitTranslations = $getUnitTranslations;
    }

    /**
     * @param ValueInterface $value
     * @param string $locale
     *
     * @return string
     */
    public function normalize(ValueInterface $value, string $locale): string
    {
        Assert::implementsInterface($value, MetricValueInterface::class);
        $context = ['locale' => $locale];

        $normalizedMetric = $this->metricNormalizer->normalize($value, 'standard', $context);

        $amount = $normalizedMetric['amount'];
        $measurementFamilyCode = $amount->getFamily();

        $metric = [
            'amount' => $amount->getData(),
            'unit' => $amount->getUnit(),
        ];

        $localizedMetric = $this->metricLocalizer->localize($metric, $context);

        return sprintf(
            '%s %s',
            $localizedMetric['amount'],
            $this->localizeUnit($measurementFamilyCode, $localizedMetric['unit'], $locale)
        );
    }

    public function supports(string $attributeType): bool
    {
        return AttributeTypes::METRIC === $attributeType;
    }

    private function localizeUnit(string $measurementFamilyCode, string $unitCode, string $locale): string
    {
        $unitTranslations = $this->getUnitTranslations->byMeasurementFamilyCodeAndLocale($measurementFamilyCode, $locale);

        if (!isset($unitTranslations[$unitCode])) {
            return $unitCode;
        }

        return $unitTranslations[$unitCode];
    }
}
