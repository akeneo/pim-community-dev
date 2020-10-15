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

    public const STATUS_ID = [
        CriterionEvaluationResultStatus::DONE => 1,
        CriterionEvaluationResultStatus::IN_PROGRESS => 2,
        CriterionEvaluationResultStatus::ERROR => 3,
        CriterionEvaluationResultStatus::NOT_APPLICABLE => 4,
    ];

    /** @var Attributes */
    private $attributes;

    /** @var Channels */
    private $channels;

    /** @var Locales */
    private $locales;

    public function __construct(Attributes $attributes, Channels $channels, Locales $locales)
    {
        $this->attributes = $attributes;
        $this->channels = $channels;
        $this->locales = $locales;
    }

    public function transformToIds(array $evaluationResult): array
    {
        $resultByIds = [];

        foreach ($evaluationResult as $propertyCode => $propertyData) {
            switch ($propertyCode) {
                case 'data':
                    $propertyDataByIds = $this->transformResultAttributeRatesCodesToIds($propertyData);
                    break;
                case 'rates':
                    $propertyDataByIds = $this->transformRatesCodesToIds($propertyData);
                    break;
                case 'status':
                    $propertyDataByIds = $this->transformStatusCodesToIds($propertyData);
                    break;
                default:
                    throw new CriterionEvaluationResultTransformationFailedException(sprintf('Unknown property code "%s"', $propertyCode));
            }

            $resultByIds[self::PROPERTIES_ID[$propertyCode]] = $propertyDataByIds;
        }

        return $resultByIds;
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
        $resultAttributeCodesRates = $resultAttributeCodesRates['attributes_with_rates'] ?? [];

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
        return $this->transformChannelLocaleDataFromCodesToIds($ratesCodes, function ($rate) {
            return $rate;
        });
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
