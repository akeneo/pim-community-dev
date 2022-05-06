<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\CriterionEvaluationResultData;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Attributes\AttributesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformChannelLocaleDataIds;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultCodes;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TransformCommonCriterionResultDataIds implements TransformResultDataIdsInterface
{
    public function __construct(
        private TransformChannelLocaleDataIds $transformChannelLocaleDataIds,
        private AttributesInterface $attributes,
    ) {
    }

    public function transformToCodes(array $resultData): array
    {
        $dataByCodes = [];
        foreach ($resultData as $dataType => $dataByIds) {
            switch ($dataType) {
                case TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['attributes_with_rates']:
                    $dataByCodes['attributes_with_rates'] = $this->transformResultAttributeRatesIdsToCodes($dataByIds);
                    break;
                case TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['total_number_of_attributes']:
                    $dataByCodes['total_number_of_attributes'] =
                        $this->transformChannelLocaleDataIds->transformToCodes($dataByIds, fn ($number) => $number);
                    break;
                case TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['number_of_improvable_attributes']:
                    $dataByCodes['number_of_improvable_attributes'] =
                        $this->transformChannelLocaleDataIds->transformToCodes($dataByIds, fn ($number) => $number);
                    break;
            }
        }

        return $dataByCodes;
    }

    private function transformResultAttributeRatesIdsToCodes(array $resultAttributeIdsRates): array
    {
        return $this->transformChannelLocaleDataIds->transformToCodes($resultAttributeIdsRates, function (array $attributeRates) {
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
    }
}
