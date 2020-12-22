<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\MetricLocalizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\MetricNormalizer as StandardMetricNormalizer;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class MetricNormalizer implements AxisValueLabelsNormalizer
{
    /** @var StandardMetricNormalizer */
    private $metricNormalizer;

    /** @var MetricLocalizer */
    private $metricLocalizer;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @todo @merge master/5.0: replace translator argument by the adequate service
     * (probably Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface)
     */
    public function __construct(
        StandardMetricNormalizer $metricNormalizer,
        MetricLocalizer $metricLocalizer,
        ?TranslatorInterface $translator = null
    ) {
        $this->metricNormalizer = $metricNormalizer;
        $this->metricLocalizer = $metricLocalizer;
        $this->translator = $translator;
    }

    /**
     * @param ValueInterface $value
     * @param string $locale
     *
     * @return string
     */
    public function normalize(ValueInterface $value, string $locale): string
    {
        $context = ['locale' => $locale];

        $normalizedMetric = $this->metricNormalizer->normalize($value, 'standard', $context);

        $metric = [
            'amount' => $normalizedMetric['amount']->getData(),
            'unit' => $value->getUnit(),
        ];

        $localizedMetric = $this->metricLocalizer->localize($metric, $context);

        return sprintf(
            '%s %s',
            $localizedMetric['amount'],
            $this->localizeUnit($localizedMetric['unit'], $locale)
        );
    }

    public function supports(string $attributeType): bool
    {
        return AttributeTypes::METRIC === $attributeType;
    }

    /**
     * @todo @merge master/5.0: rework this method
     * it should probably use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface
     */
    private function localizeUnit(string $unit, string $locale): string
    {
        if (null === $this->translator) {
            return $unit;
        }

        $translationKey = sprintf('pim_measure.units.%s', $unit);
        $translation = $this->translator->trans($translationKey, [], 'messages', $locale);

        return ($translation === $translationKey) ? $unit : $translation;
    }
}
