<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Transformation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultCodes;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TransformCriterionEvaluationResultCodesIntegration extends DataQualityInsightsTestCase
{
    public function test_it_transforms_a_criterion_evaluation_result_from_codes_to_ids()
    {
        $ecommerceId = $this->createChannel('ecommerce', ['locales' => ['en_US', 'fr_FR']])->getId();
        $mobileId = $this->createChannel('mobile', ['locales' => ['en_US', 'de_DE']])->getId();
        $enUsId = $this->getLocaleId('en_US');
        $frFrId = $this->getLocaleId('fr_FR');
        $deDeId = $this->getLocaleId('de_DE');
        $nameId = $this->createAttribute('name')->getId();
        $descriptionId = $this->createAttribute('description')->getId();

        $evaluationResult = [
            'data' => [
                'attributes_with_rates' => [
                    'ecommerce' => [
                        'en_US' => [
                            'name' => 50,
                            'description' => 0,
                        ],
                        'fr_FR' => [
                            'description' => 20,
                        ],
                    ],
                    'mobile' => [
                        'en_US' => ['name' => 0],
                        'de_DE' => [],
                    ],
                ],
                'total_number_of_attributes' => [
                    'ecommerce' => [
                        'en_US' => 4,
                        'fr_FR' => 4,
                    ],
                    'mobile' => [
                        'en_US' => 6,
                        'de_DE' => 0,
                    ],
                ],
            ],
            'rates' => [
                'ecommerce' => [
                    'en_US' => 25,
                    'fr_FR' => 75,
                ],
                'mobile' => [
                    'en_US' => 100,
                    'de_DE' => null,
                ],
            ],
            'status' => [
                'ecommerce' => [
                    'en_US' => CriterionEvaluationResultStatus::DONE,
                    'fr_FR' => CriterionEvaluationResultStatus::IN_PROGRESS,
                ],
                'mobile' => [
                    'en_US' => CriterionEvaluationResultStatus::ERROR,
                    'de_DE' => CriterionEvaluationResultStatus::NOT_APPLICABLE,
                ],
            ]
        ];

        $expectedEvaluationResult = [
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['data'] => [
                TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['attributes_with_rates'] => [
                    $ecommerceId => [
                        $enUsId => [
                            $nameId => 50,
                            $descriptionId => 0,
                        ],
                        $frFrId => [
                            $descriptionId => 20,
                        ],
                    ],
                    $mobileId => [
                        $enUsId => [$nameId => 0],
                        $deDeId => [],
                    ],
                ],
                TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['total_number_of_attributes'] => [
                    $ecommerceId => [
                        $enUsId => 4,
                        $frFrId => 4,
                    ],
                    $mobileId => [
                        $enUsId => 6,
                        $deDeId => 0,
                    ],
                ],
            ],
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['rates'] => [
                $ecommerceId => [
                    $enUsId => 25,
                    $frFrId => 75,
                ],
                $mobileId => [
                    $enUsId => 100,
                    $deDeId => null,
                ],
            ],
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['status'] => [
                $ecommerceId => [
                    $enUsId => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::DONE],
                    $frFrId => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::IN_PROGRESS],
                ],
                $mobileId => [
                    $enUsId => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::ERROR],
                    $deDeId => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::NOT_APPLICABLE],
                ],
            ]
        ];

        $convertedEvaluationResult = $this->get(TransformCriterionEvaluationResultCodes::class)->transformToIds($evaluationResult);

        $this->assertEquals($expectedEvaluationResult, $convertedEvaluationResult);
    }
}
