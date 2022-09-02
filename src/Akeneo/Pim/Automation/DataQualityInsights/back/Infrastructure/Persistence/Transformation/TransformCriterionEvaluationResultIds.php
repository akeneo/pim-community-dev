<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\CriterionEvaluationResultData\TransformResultDataIdsInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TransformCriterionEvaluationResultIds
{
    public function __construct(
        private TransformChannelLocaleDataIds   $transformChannelLocaleDataIds,
        private TransformResultDataIdsInterface $transformCommonCriterionResultData,
        private TransformResultDataIdsInterface $transformCompletenessResultData,
    ) {
    }

    /**
     * Example of array returned (with one channel and one locale)
     * [
     *   'data' => [
     *     'total_number_of_attributes' => [
     *       'ecommerce' => [
     *         'en_US' => 5,
     *       ],
     *     ],
     *     'number_of_improvable_attributes' => [
     *       'ecommerce' => [
     *         'en_US' => 2,
     *       ],
     *     ],
     *   ],
     *   'rates' => [
     *     'ecommerce' => [
     *       'en_US' => 60,
     *     ],
     *   ],
     *   'status' => [
     *     'ecommerce' => [
     *       'en_US' => 'done',
     *     ],
     *   ],
     * ]
     */
    public function transformToCodes(CriterionCode $criterionCode, array $evaluationResult): array
    {
        $resultByCodes = [];
        $propertiesIds = TransformCriterionEvaluationResultCodes::PROPERTIES_ID;
        $propertiesCodes = array_flip($propertiesIds);

        foreach ($evaluationResult as $propertyId => $propertyData) {
            switch ($propertyId) {
                case $propertiesIds['data']:
                    $propertyDataByCodes = $this->getTransformResultData($criterionCode)->transformToCodes($propertyData);
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

    private function transformRatesIdsToCodes(array $ratesIds): array
    {
        return $this->transformChannelLocaleDataIds->transformToCodes($ratesIds, function ($rate) {
            return $rate;
        });
    }

    private function transformStatusIdsToCodes(array $statusIds): array
    {
        $statusCodes = array_flip(TransformCriterionEvaluationResultCodes::STATUS_ID);
        return $this->transformChannelLocaleDataIds->transformToCodes($statusIds, function ($statusId) use ($statusCodes) {
            if (!isset($statusCodes[$statusId])) {
                throw new CriterionEvaluationResultTransformationFailedException(sprintf('Unknown status id "%s"', $statusId));
            }

            return $statusCodes[$statusId];
        });
    }

    public function getTransformResultData(CriterionCode $criterionCode): TransformResultDataIdsInterface
    {
        $stringCriterionCode = \strval($criterionCode);

        if (EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE === $stringCriterionCode
            || EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE === $stringCriterionCode) {
            return $this->transformCompletenessResultData;
        }

        return $this->transformCommonCriterionResultData;
    }
}
