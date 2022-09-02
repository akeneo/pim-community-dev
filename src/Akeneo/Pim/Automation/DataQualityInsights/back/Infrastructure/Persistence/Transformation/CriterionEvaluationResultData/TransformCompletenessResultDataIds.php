<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\CriterionEvaluationResultData;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformChannelLocaleDataIds;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultCodes;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TransformCompletenessResultDataIds implements TransformResultDataIdsInterface
{
    public function __construct(
        private TransformChannelLocaleDataIds $transformChannelLocaleDataIds,
    ) {
    }

    public function transformToCodes(array $resultData): array
    {
        $dataByCodes = [];

        $numberOfImprovableAttributes = $resultData[TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['number_of_improvable_attributes']] ?? null;
        $improvableAttributes = $resultData[TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['attributes_with_rates']] ?? null;
        $totalNumberOfAttributes = $resultData[TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['total_number_of_attributes']] ?? null;

        /**
         * Since PLG-468 the list of the improvable attributes are no longer persisted. Only their number is persisted in the key "number_of_improvable_attributes"
         * If the criterion has not been re-evaluated since this change, its result data still contains the key "attributes_with_rates"
         * In this case the transformation consists in counting the number of elements in "attributes_with_rates" and putting this number in the key "number_of_improvable_attributes"
         */
        if (null !== $numberOfImprovableAttributes) {
            $dataByCodes['number_of_improvable_attributes'] = $this->transformChannelLocaleDataIds->transformToCodes($numberOfImprovableAttributes, fn ($number) => $number);
        } elseif (null !== $improvableAttributes) {
            $dataByCodes['number_of_improvable_attributes'] = $this->transformChannelLocaleDataIds->transformToCodes($improvableAttributes, fn (array $attributesList) => count($attributesList));
        }

        if (null !== $totalNumberOfAttributes) {
            $dataByCodes['total_number_of_attributes'] = $this->transformChannelLocaleDataIds->transformToCodes($totalNumberOfAttributes, fn ($total) => $total);
        }

        return $dataByCodes;
    }
}
