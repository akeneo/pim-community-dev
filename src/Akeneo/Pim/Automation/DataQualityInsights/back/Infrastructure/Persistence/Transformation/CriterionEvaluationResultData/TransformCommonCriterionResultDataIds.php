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
                    $dataByCodes['attributes_with_rates'] = $this->transformResultAttributeDataIdsToCodes($dataByIds);
                    break;
                case TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['total_number_of_attributes']:
                    $dataByCodes['total_number_of_attributes'] =
                        $this->transformChannelLocaleDataIds->transformToCodes($dataByIds, fn ($number) => $number);
                    break;
                case TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['number_of_improvable_attributes']:
                    $dataByCodes['number_of_improvable_attributes'] =
                        $this->transformChannelLocaleDataIds->transformToCodes($dataByIds, fn ($number) => $number);
                    break;
                case TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['hashed_values']:
                    $dataByCodes['hashed_values'] = $this->transformResultAttributeDataIdsToCodes($dataByIds);
                    break;
            }
        }

        return $dataByCodes;
    }

    private function transformResultAttributeDataIdsToCodes(array $resultAttributeIdsData): array
    {
        return $this->transformChannelLocaleDataIds->transformToCodes($resultAttributeIdsData, function (array $attributeData) {
            $attributeCodesData = [];
            $attributesCodes = $this->attributes->getCodesByIds(array_keys($attributeData));

            foreach ($attributeData as $attributeId => $data) {
                $attributeCode = $attributesCodes[$attributeId] ?? null;
                if (null !== $attributeCode) {
                    $attributeCodesData[$attributeCode] = $data;
                }
            }

            return $attributeCodesData;
        });
    }
}
