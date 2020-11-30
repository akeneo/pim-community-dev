<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TransformCriterionEvaluationResultIds
{
    private Attributes $attributes;

    private Channels $channels;

    private Locales $locales;

    public function __construct(Attributes $attributes, Channels $channels, Locales $locales)
    {
        $this->attributes = $attributes;
        $this->channels = $channels;
        $this->locales = $locales;
    }

    public function transformToCodes(array $evaluationResult): array
    {
        $resultByCodes = [];
        $propertiesIds = TransformCriterionEvaluationResultCodes::PROPERTIES_ID;
        $propertiesCodes = array_flip($propertiesIds);

        foreach ($evaluationResult as $propertyId => $propertyData) {
            switch ($propertyId) {
                case $propertiesIds['data']:

                    $propertyDataByCodes = $this->transformResultAttributeRatesIdsToCodes($propertyData);
                    break;
                case $propertiesIds['rates']:
                    $propertyDataByCodes = $this->transformRatesIdsToCodes($propertyData);
                    break;
                case $propertiesIds['status']:
                    $propertyDataByCodes = $this->transformStatusIdsToCodes($propertyData);
                    break;
                default:
                    throw new CriterionEvaluationResultTransformationFailedException(sprintf('Unknown property id "%s"', $propertyId));
            }

            $resultByCodes[$propertiesCodes[$propertyId]] = $propertyDataByCodes;
        }

        return $resultByCodes;
    }

    private function transformChannelLocaleDataFromIdsToCodes(array $channelLocaleData, \Closure $transformData): array
    {
        $channelLocaleDataByCodes = [];

        foreach ($channelLocaleData as $channelId => $localeData) {
            $channelCode = $this->channels->getCodeById($channelId);
            if (null === $channelCode) {
                continue;
            }

            foreach ($localeData as $localeId => $data) {
                $localeCode = $this->locales->getCodeById($localeId);
                if (null === $localeCode) {
                    continue;
                }

                $channelLocaleDataByCodes[$channelCode][$localeCode] = $transformData($data);
            }
        }

        return $channelLocaleDataByCodes;
    }

    private function transformResultAttributeRatesIdsToCodes(array $resultAttributeIdsRates): array
    {
        $attributesRates = $this->transformChannelLocaleDataFromIdsToCodes($resultAttributeIdsRates, function (array $attributeRates) {
            $attributeCodesRates = [];
            $attributesCodes = $this->attributes->getCodesByIds(array_keys($attributeRates));

            foreach ($attributeRates as $attributeId => $attributeRate) {
                $attributeCode = $attributesCodes[$attributeId] ?? null;
                if (null !== $attributeCode) {
                    $attributeCodesRates[$attributeCode] = $attributeRate;
                }
            }

            return $attributeCodesRates;
        });

        return empty($attributesRates) ? [] : ['attributes_with_rates' => $attributesRates];
    }

    private function transformRatesIdsToCodes(array $ratesIds): array
    {
        return $this->transformChannelLocaleDataFromIdsToCodes($ratesIds, function ($rate) {
            return $rate;
        });
    }

    private function transformStatusIdsToCodes(array $statusIds): array
    {
        $statusCodes = array_flip(TransformCriterionEvaluationResultCodes::STATUS_ID);
        return $this->transformChannelLocaleDataFromIdsToCodes($statusIds, function ($statusId) use ($statusCodes) {
            if (!isset($statusCodes[$statusId])) {
                throw new CriterionEvaluationResultTransformationFailedException(sprintf('Unknown status id "%s"', $statusId));
            }

            return $statusCodes[$statusId];
        });
    }
}
