<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\CriterionEvaluationResultData;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Attributes\InMemoryAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Channels\InMemoryChannels;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Locales\InMemoryLocales;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformChannelLocaleDataIds;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultCodes;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformCommonCriterionResultDataIdsSpec extends ObjectBehavior
{
    public function let()
    {
        $attributes = new InMemoryAttributes([
            'name' => 12,
            'description' => 34,
        ]);

        $channels = new InMemoryChannels([
            'ecommerce' => 1,
            'mobile' => 2,
        ]);
        $locales = new InMemoryLocales([
            'en_US' => 58,
            'fr_FR' => 90,
        ]);

        $transformChannelLocaleDataIds = new TransformChannelLocaleDataIds($channels, $locales);

        $this->beConstructedWith($transformChannelLocaleDataIds, $attributes);
    }

    public function it_transforms_common_criterion_result_data_from_ids_to_codes()
    {
        $dataToTransform = [
            TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['attributes_with_rates'] => [
                1 => [
                    58 => [
                        12 => 50,
                        34 => 0,
                    ],
                    90 => [
                        34 => 20,
                    ],
                ],
            ],
            TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['total_number_of_attributes'] => [
                1 => [
                    58 => 4,
                    90 => 5,
                ],
            ],
            TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['number_of_improvable_attributes'] => [
                1 => [
                    58 => 2,
                    90 => 1,
                ],
            ],
        ];

        $expectedTransformedData = [
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
            ],
            'total_number_of_attributes' => [
                'ecommerce' => [
                    'en_US' => 4,
                    'fr_FR' => 5,
                ],
            ],
            'number_of_improvable_attributes' => [
                'ecommerce' => [
                    'en_US' => 2,
                    'fr_FR' => 1,
                ],
            ],
        ];

        $this->transformToCodes($dataToTransform)->shouldReturn($expectedTransformedData);
    }

    public function it_removes_unknown_attributes_from_result_data(): void
    {
        $dataToTransform = [
            TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['attributes_with_rates'] => [
                1 => [
                    58 => [
                        12 => 50,
                        42 => 76,
                        34 => 0,
                    ],
                    90 => [
                        34 => 20,
                    ],
                ],
            ],
        ];

        $expectedTransformedData = [
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
            ],
        ];

        $this->transformToCodes($dataToTransform)->shouldReturn($expectedTransformedData);
    }
}
