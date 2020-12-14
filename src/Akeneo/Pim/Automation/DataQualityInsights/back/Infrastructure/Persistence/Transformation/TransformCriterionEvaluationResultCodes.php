<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TransformCriterionEvaluationResultCodes
{
    public const PROPERTIES_ID = [
        'data' => 1,
        'rates' => 2,
        'status' => 3,
    ];

    public const DATA_TYPES_ID = [
        'attributes_with_rates' => 1,
        'total_number_of_attributes' => 2,
    ];

    public const STATUS_ID = [
        CriterionEvaluationResultStatus::DONE => 1,
        CriterionEvaluationResultStatus::IN_PROGRESS => 2,
        CriterionEvaluationResultStatus::ERROR => 3,
        CriterionEvaluationResultStatus::NOT_APPLICABLE => 4,
    ];

    private Attributes $attributes;

    private Channels $channels;

    private Locales $locales;

    public function __construct(Attributes $attributes, Channels $channels, Locales $locales)
    {
        $this->attributes = $attributes;
        $this->channels = $channels;
        $this->locales = $locales;
    }

    public function transformToIds(array $evaluationResult): array
    {
        $resultByIds = [];

        foreach ($evaluationResult as $propertyCode => $propertyValues) {
            switch ($propertyCode) {
                case 'data':
                    $propertyDataByIds = $this->transformResultDataCodesToIds($propertyValues);
                    break;
                case 'rates':
                    $propertyDataByIds = $this->transformRatesCodesToIds($propertyValues);
                    break;
                case 'status':
                    $propertyDataByIds = $this->transformStatusCodesToIds($propertyValues);
                    break;
                default:
                    throw new CriterionEvaluationResultTransformationFailedException(sprintf('Unknown property code "%s"', $propertyCode));
            }

            $resultByIds[self::PROPERTIES_ID[$propertyCode]] = $propertyDataByIds;
        }

        return $resultByIds;
    }

    private function transformResultDataCodesToIds(array $resultDataByCodes): array
    {
        $resultDataByIds = [];
        foreach ($resultDataByCodes as $dataType => $dataByCodes) {
            switch ($dataType) {
                case 'attributes_with_rates':
                    $resultDataByIds[self::DATA_TYPES_ID['attributes_with_rates']] =
                        $this->transformResultAttributeRatesCodesToIds($dataByCodes);
                    break;
                case 'total_number_of_attributes':
                    $resultDataByIds[self::DATA_TYPES_ID['total_number_of_attributes']] =
                        $this->transformChannelLocaleDataFromCodesToIds($dataByCodes, fn ($number) => $number);
                    break;
                default:
                    throw new CriterionEvaluationResultTransformationFailedException(sprintf('Unknown result data type "%s"', $dataType));
            }
        }

        return $resultDataByIds;
    }

    private function transformChannelLocaleDataFromCodesToIds(array $channelLocaleData, \Closure $transformData): array
    {
        $channelLocaleDataByIds = [];

        foreach ($channelLocaleData as $channel => $localeData) {
            $channelId = $this->channels->getIdByCode($channel);
            if (null === $channelId) {
                continue;
            }

            foreach ($localeData as $locale => $data) {
                $localeId = $this->locales->getIdByCode($locale);
                if (null === $localeId) {
                    continue;
                }

                $channelLocaleDataByIds[$channelId][$localeId] = $transformData($data);
            }
        }

        return $channelLocaleDataByIds;
    }

    private function transformResultAttributeRatesCodesToIds(array $resultAttributeCodesRates): array
    {
        return $this->transformChannelLocaleDataFromCodesToIds($resultAttributeCodesRates, function (array $attributeRates) {
            $attributeIdsRates = [];
            $attributesIds = $this->attributes->getIdsByCodes(array_keys($attributeRates));

            foreach ($attributeRates as $attributeCode => $attributeRate) {
                $attributeId = $attributesIds[$attributeCode] ?? null;
                if (null !== $attributeId) {
                    $attributeIdsRates[$attributeId] = $attributeRate;
                }
            }

            return $attributeIdsRates;
        });
    }

    private function transformRatesCodesToIds(array $ratesCodes): array
    {
        return $this->transformChannelLocaleDataFromCodesToIds($ratesCodes, fn ($rate) => $rate);
    }

    private function transformStatusCodesToIds(array $statusCodes): array
    {
        return $this->transformChannelLocaleDataFromCodesToIds($statusCodes, function (string $statusCode) {
            if (!isset(self::STATUS_ID[$statusCode])) {
                throw new CriterionEvaluationResultTransformationFailedException(sprintf('Unknown status code "%s"', $statusCode));
            }

            return self::STATUS_ID[$statusCode];
        });
    }
}
