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
final class TransformCriterionEvaluationResultIdsSpec extends ObjectBehavior
{
    public function let(Attributes $attributes, Channels $channels, Locales $locales)
    {
        $this->beConstructedWith($attributes, $channels, $locales);

        $attributes->getCodesByIds([12, 34])->willReturn([12 => 'name', 34 => 'description']);
        $attributes->getCodesByIds([34])->willReturn([34 => 'description']);

        $channels->getCodeById(1)->willReturn('ecommerce');
        $locales->getCodeById(58)->willReturn('en_US');
        $locales->getCodeById(90)->willReturn('fr_FR');
    }

    public function it_transforms_a_criterion_evaluation_result_from_ids_to_codes()
    {
        $criterionEvaluationResultIds = [
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

        $this->transformToCodes($criterionEvaluationResultIds)->shouldBeLike($this->getExpectedResult());
    }

    public function it_throws_an_exception_if_the_evaluation_result_has_an_unknown_property()
    {
        $invalidEvaluationResult = [
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['data'] => [
                1 => [
                    1 => [
                        58 => [
                            12 => 50,
                            34 => 0,
                        ],
                    ],
                ],
            ],
            999 => [
                1 => [
                    58 => 25,
                    90 => 75,
                ],
            ],
        ];

        $this->shouldThrow(CriterionEvaluationResultTransformationFailedException::class)->during('transformToCodes', [$invalidEvaluationResult]);
    }

    public function it_throws_an_exception_if_the_evaluation_result_has_an_unknown_status()
    {
        $invalidEvaluationResult = [
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
                ]
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
                    90 => 123456,
                ],
            ]
        ];

        $this->shouldThrow(CriterionEvaluationResultTransformationFailedException::class)->during('transformToCodes', [$invalidEvaluationResult]);
    }

    public function it_throws_an_exception_if_the_evaluation_result_has_an_unknown_data_type()
    {
        $invalidEvaluationResult = [
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
                42 => [
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
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['status'] => []
        ];

        $this->shouldThrow(CriterionEvaluationResultTransformationFailedException::class)->during('transformToCodes', [$invalidEvaluationResult]);
    }

    public function it_removes_unknown_channels($channels)
    {
        $channels->getCodeById(42)->willReturn(null);

        $criterionEvaluationResultIds = [
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
                    42 => [
                        58 => [
                            12 => 100,
                        ],
                    ],
                ],
                2 => [
                    1 => [
                        58 => 4,
                        90 => 5,
                    ],
                    42 => [
                        58 => 6,
                    ],
                ]
            ],
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['rates'] => [
                1 => [
                    58 => 25,
                    90 => 75,
                ],
                42 => [
                    58 => 100,
                    90 => 0,
                ],
            ],
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['status'] => [
                1 => [
                    58 => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::DONE],
                    90 => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::IN_PROGRESS],
                ],
                42 => [
                    58 => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::DONE],
                    90 => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::DONE],
                ],
            ]
        ];

        $this->transformToCodes($criterionEvaluationResultIds)->shouldBeLike($this->getExpectedResult());
    }

    public function it_removes_unknown_locales($locales)
    {
        $locales->getCodeById(987)->willReturn(null);

        $criterionEvaluationResultIds = [
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
                        987 => [
                            12 => 56,
                        ]
                    ],
                ],
                2 => [
                    1 => [
                        58 => 4,
                        90 => 5,
                        987 => 6
                    ],
                ],
            ],
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['rates'] => [
                1 => [
                    58 => 25,
                    90 => 75,
                    987 => 49,
                ],
            ],
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['status'] => [
                1 => [
                    58 => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::DONE],
                    90 => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::IN_PROGRESS],
                    987 => TransformCriterionEvaluationResultCodes::STATUS_ID[CriterionEvaluationResultStatus::DONE],
                ],
            ]
        ];

        $this->transformToCodes($criterionEvaluationResultIds)->shouldBeLike($this->getExpectedResult());
    }

    private function getExpectedResult(): array
    {
        return [
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
    }
}
