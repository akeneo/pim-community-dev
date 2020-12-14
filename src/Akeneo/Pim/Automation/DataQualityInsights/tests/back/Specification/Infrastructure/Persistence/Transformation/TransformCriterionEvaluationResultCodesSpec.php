<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Attributes;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Channels;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\CriterionEvaluationResultTransformationFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Locales;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultCodes;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TransformCriterionEvaluationResultCodesSpec extends ObjectBehavior
{
    public function let(Attributes $attributes, Channels $channels, Locales $locales)
    {
        $this->beConstructedWith($attributes, $channels, $locales);

        $attributes->getIdsByCodes(['name', 'description'])->willReturn(['name' => 12, 'description' => 34]);
        $attributes->getIdsByCodes(['description'])->willReturn(['description' => 34]);

        $channels->getIdByCode('ecommerce')->willReturn(1);
        $locales->getIdByCode('en_US')->willReturn(58);
        $locales->getIdByCode('fr_FR')->willReturn(90);
    }

    public function it_transforms_a_criterion_evaluation_result_from_codes_to_ids()
    {
        $criterionEvaluationResultCodes = [
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
                ],
                'total_number_of_attributes' => [
                    'ecommerce' => [
                        'en_US' => 4,
                        'fr_FR' => 5,
                    ],
                ],
            ],
            'rates' => [
                'ecommerce' => [
                    'en_US' => 25,
                    'fr_FR' => 75,
                ],
            ],
            'status' => [
                'ecommerce' => [
                    'en_US' => CriterionEvaluationResultStatus::DONE,
                    'fr_FR' => CriterionEvaluationResultStatus::IN_PROGRESS,
                ],
            ]
        ];

        $this->transformToIds($criterionEvaluationResultCodes)->shouldBeLike($this->getExpectedResult());
    }

    public function it_throws_an_exception_if_the_evaluation_result_has_an_unknown_property()
    {
        $invalidEvaluationResult = [
            'rates' => [
                'ecommerce' => [
                    'en_US' => 25,
                    'fr_FR' => 75,
                ],
            ],
            'foo' => [
                'ecommerce' => [
                    'en_US' => 'done',
                    'fr_FR' => 'in_progress',
                ],
            ]
        ];

        $this->shouldThrow(CriterionEvaluationResultTransformationFailedException::class)->during('transformToIds', [$invalidEvaluationResult]);
    }

    public function it_throws_an_exception_if_the_evaluation_result_has_an_unknown_status()
    {
        $invalidEvaluationResult = [
            'data' => [
                'attributes_with_rates' => [
                    'ecommerce' => [
                        'en_US' => [
                            'name' => 50,
                            'description' => 0,
                        ],
                    ],
                ],
            ],
            'rates' => [
                'ecommerce' => [
                    'en_US' => 25,
                    'fr_FR' => null,
                ],
            ],
            'status' => [
                'ecommerce' => [
                    'en_US' => CriterionEvaluationResultStatus::DONE,
                    'fr_FR' => 'foo',
                ],
            ]
        ];

        $this->shouldThrow(CriterionEvaluationResultTransformationFailedException::class)->during('transformToIds', [$invalidEvaluationResult]);
    }

    public function it_throws_an_exception_if_the_evaluation_result_has_an_unknown_data_type()
    {
        $invalidEvaluationResult = [
            'data' => [
                'attributes_with_rates' => [
                    'ecommerce' => [
                        'en_US' => [
                            'name' => 50,
                            'description' => 0,
                        ],
                    ],
                ],
                'foo' => [
                    'ecommerce' => [
                        'en_US' => 4,
                        'fr_FR' => 5,
                    ],
                ],
            ],
            'rates' => [],
            'status' => [],
        ];

        $this->shouldThrow(CriterionEvaluationResultTransformationFailedException::class)->during('transformToIds', [$invalidEvaluationResult]);
    }

    public function it_removes_unknown_channels($channels)
    {
        $channels->getIdByCode('foo')->willReturn(null);

        $criterionEvaluationResultCodes = [
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
                    'foo' => [
                        'en_US' => [
                            'name' => 50,
                        ],
                    ],
                ],
                'total_number_of_attributes' => [
                    'ecommerce' => [
                        'en_US' => 4,
                        'fr_FR' => 5,
                    ],
                    'foo' => [
                        'en_US' => 3,
                    ],
                ],
            ],
            'rates' => [
                'ecommerce' => [
                    'en_US' => 25,
                    'fr_FR' => 75,
                ],
                'foo' => [
                    'en_US' => 56,
                ],
            ],
            'status' => [
                'ecommerce' => [
                    'en_US' => CriterionEvaluationResultStatus::DONE,
                    'fr_FR' => CriterionEvaluationResultStatus::IN_PROGRESS,
                ],
                'foo' => [
                    'en_US' => CriterionEvaluationResultStatus::DONE,
                ],
            ]
        ];

        $this->transformToIds($criterionEvaluationResultCodes)->shouldBeLike($this->getExpectedResult());
    }

    public function it_removes_unknown_locales($locales)
    {
        $locales->getIdByCode('fo_FO')->willReturn(null);

        $criterionEvaluationResultCodes = [
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
                        'fo_FO' => [
                            'name' => 80,
                        ],
                    ],
                ],
                'total_number_of_attributes' => [
                    'ecommerce' => [
                        'en_US' => 4,
                        'fr_FR' => 5,
                        'fo_FO' => 3,
                    ],
                ],
            ],
            'rates' => [
                'ecommerce' => [
                    'en_US' => 25,
                    'fr_FR' => 75,
                    'fo_FO' => 89,
                ],
            ],
            'status' => [
                'ecommerce' => [
                    'en_US' => CriterionEvaluationResultStatus::DONE,
                    'fr_FR' => CriterionEvaluationResultStatus::IN_PROGRESS,
                    'fo_FO' => CriterionEvaluationResultStatus::DONE,
                ],
            ]
        ];

        $this->transformToIds($criterionEvaluationResultCodes)->shouldBeLike($this->getExpectedResult());
    }

    private function getExpectedResult(): array
    {
        return [
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['data'] => [
                1 => [
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
                2 => [
                    1 => [
                        58 => 4,
                        90 => 5,
                    ],
                ],
            ],
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['rates'] => [
                1 => [
                    58 => 25,
                    90 => 75,
                ],
            ],
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['status'] => [
                1 => [
                    58 => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::DONE],
                    90 => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::IN_PROGRESS],
                ],
            ]
        ];
    }
}
