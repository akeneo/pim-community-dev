<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\GetUnitTranslations;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class MetricValueStringifier extends AbstractValueStringifier implements ValueStringifierInterface
{
    public const UNIT_LABEL_LOCALE_KEY = 'unit_label_locale';

    private GetUnitTranslations $getUnitTranslations;
    private array $cacheUnitTranslations = [];

    public function __construct(GetUnitTranslations $getUnitTranslations, array $attributeTypes)
    {
        parent::__construct($attributeTypes);

        $this->getUnitTranslations = $getUnitTranslations;
    }

    /**
     * {@inheritDoc}
     */
    public function stringify(ValueInterface $value, array $options = []): string
    {
        Assert::isInstanceOf($value, MetricValueInterface::class);
        $unitCode = $value->getUnit();
        if (null === $value->getAmount() || null === $unitCode) {
            return '';
        }

        $unitLabelLocaleCode = $options[static::UNIT_LABEL_LOCALE_KEY] ?? null;
        $unitLabel = null;
        if (null !== $unitLabelLocaleCode) {
            $metric = $value->getData();

            if (null !== $metric) {
                $unitTranslations = $this->getUnitTranslationsForMeasurementFamilyAndLocale(
                    $metric->getFamily(),
                    $unitLabelLocaleCode
                );
                $unitLabel = $unitTranslations[$unitCode] ?? null;
            }
        }

        return sprintf('%s %s', $this->formatNumber($value->getAmount()), $unitLabel ?? $unitCode);
    }

    /**
     * If number is an integer, should returns the number without decimal
     * If number is a float, should returns the number without ending 0.
     *
     * @param string $number
     * @return string
     */
    private function formatNumber(string $number): string
    {
        if (strpos($number, '.') === false) {
            return $number;
        }

        return preg_replace('/\.?0*$/', '', $number);
    }

    private function getUnitTranslationsForMeasurementFamilyAndLocale(
        string $measurementFamilyCode,
        string $localeCode
    ): array {
        $key = sprintf('%s-%s', $measurementFamilyCode, $localeCode);

        if (!array_key_exists($key, $this->cacheUnitTranslations)) {
            $this->cacheUnitTranslations[$key] = $this->getUnitTranslations->byMeasurementFamilyCodeAndLocale(
                $measurementFamilyCode,
                $localeCode
            );
        }

        return $this->cacheUnitTranslations[$key];
    }
}
