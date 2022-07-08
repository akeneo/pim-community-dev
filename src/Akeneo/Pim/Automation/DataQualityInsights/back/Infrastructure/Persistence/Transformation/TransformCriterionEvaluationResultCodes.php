<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Attributes\AttributesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Channels\ChannelsInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Locales\LocalesInterface;

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
        'number_of_improvable_attributes' => 3,
        'hashed_values' => 4,
    ];

    public const STATUS_ID = [
        CriterionEvaluationResultStatus::DONE => 1,
        CriterionEvaluationResultStatus::IN_PROGRESS => 2,
        CriterionEvaluationResultStatus::ERROR => 3,
        CriterionEvaluationResultStatus::NOT_APPLICABLE => 4,
    ];

    public function __construct(
        private AttributesInterface $attributes,
        private ChannelsInterface $channels,
        private LocalesInterface $locales
    ) {
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
                case 'hashed_values':
                    $resultDataByIds[self::DATA_TYPES_ID[$dataType]] =
                        $this->transformChannelLocaleDataByAttributesFromCodesToIds($dataByCodes);
                    break;
                case 'number_of_improvable_attributes':
                case 'total_number_of_attributes':
                    $resultDataByIds[self::DATA_TYPES_ID[$dataType]] =
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

    private function transformChannelLocaleDataByAttributesFromCodesToIds(array $dataByAttributes): array
    {
        return $this->transformChannelLocaleDataFromCodesToIds($dataByAttributes, function (array $attributeData) {
            $attributeIdsData = [];
            $attributesIds = $this->attributes->getIdsByCodes(array_keys($attributeData));

            foreach ($attributeData as $attributeCode => $data) {
                $attributeId = $attributesIds[$attributeCode] ?? null;
                if (null !== $attributeId) {
                    $attributeIdsData[$attributeId] = $data;
                }
            }

            return $attributeIdsData;
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
