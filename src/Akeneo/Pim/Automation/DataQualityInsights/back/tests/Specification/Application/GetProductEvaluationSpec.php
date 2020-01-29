<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\EvaluateSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text\EvaluateTitleFormatting;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class GetProductEvaluationSpec extends ObjectBehavior
{
    public function let(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery,
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery
    ) {
        $this->beConstructedWith($getLatestCriteriaEvaluationsByProductIdQuery, $getLocalesByChannelQuery);

        $getLocalesByChannelQuery->execute()->willReturn([
            'ecommerce' => ['en_US', 'fr_FR'],
            'mobile' => ['en_US']
        ]);
    }

    public function it_gets_the_evaluations_of_a_product(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery
    ) {
        $productId = new ProductId(2000);
        $rawEvaluation = $this->generateCompleteEvaluation($productId);
        $expectedEvaluation = $this->generateExpectedCompleteEvaluation();

        $getLatestCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($rawEvaluation);

        $this->get($productId)->shouldBeLike($expectedEvaluation);
    }

    private function generateCriterionEvaluation(ProductId $productId, string $code, string $status, CriterionRateCollection $resultRates, array $resultData)
    {
        return new CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode($code),
            $productId,
            new \DateTimeImmutable(),
            new CriterionEvaluationStatus($status),
            new CriterionEvaluationResult($resultRates, $resultData),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );
    }

    private function generateCompleteEvaluation(ProductId $productId): CriterionEvaluationCollection
    {
        $channelCodeEcommerce = new ChannelCode('ecommerce');
        $channelCodeMobile = new ChannelCode('mobile');
        $localeCodeEn = new LocaleCode('en_US');
        $localeCodeFr = new LocaleCode('fr_FR');

        $completenessOfNonRequiredAttributesRates = new CriterionRateCollection();
        $completenessOfNonRequiredAttributesRates
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(50))
        ;
        $completenessOfNonRequiredAttributesData = [
            "attributes" => [
                "ecommerce" => [
                    "en_US" => ["title", "meta_title"]
                ]
            ]
        ];

        $completenessOfRequiredAttributesRates = new CriterionRateCollection();
        $completenessOfRequiredAttributesRates
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(100))
        ;
        $completenessOfRequiredAttributesData = [
            "attributes" => [
                "ecommerce" => []
            ]
        ];

        $evaluateSpellingRates = new CriterionRateCollection();
        $evaluateSpellingRates
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(88))
            ->addRate($channelCodeEcommerce, $localeCodeFr, new Rate(68))
            ->addRate($channelCodeMobile, $localeCodeEn, new Rate(76))
        ;
        $evaluateSpellingData = [
            "attributes" => [
                "ecommerce" => [
                    "en_US" => ["description"],
                    "fr_FR" => ["description", "short_description"],
                ]
            ]
        ];

        $evaluateTitleFormattingRates = new CriterionRateCollection();
        $evaluateTitleFormattingRates
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(85))
            ->addRate($channelCodeMobile, $localeCodeEn, new Rate(0))
        ;
        $evaluateTitleFormattingData = [
            "attributes" => [
                "ecommerce" => [
                    "en_US" => ["title"],
                ],
                "mobile" => [
                    "en_US" => ["title", "meta_title"]
                ]
            ]
        ];

        $evaluation = new CriterionEvaluationCollection();
        $evaluation
            ->add($this->generateCriterionEvaluation(
                $productId,
                EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE,
                CriterionEvaluationStatus::DONE,
                $completenessOfNonRequiredAttributesRates,
                $completenessOfNonRequiredAttributesData
            ))
            ->add($this->generateCriterionEvaluation(
                $productId,
                EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
                CriterionEvaluationStatus::DONE,
                $completenessOfRequiredAttributesRates,
                $completenessOfRequiredAttributesData
            ))
            ->add($this->generateCriterionEvaluation(
                $productId,
                EvaluateSpelling::CRITERION_CODE,
                CriterionEvaluationStatus::DONE,
                $evaluateSpellingRates,
                $evaluateSpellingData
            ))
            ->add($this->generateCriterionEvaluation(
                $productId,
                EvaluateTitleFormatting::CRITERION_CODE,
                CriterionEvaluationStatus::DONE,
                $evaluateTitleFormattingRates,
                $evaluateTitleFormattingData
            ))
        ;

        return $evaluation;
    }

    private function generateExpectedCompleteEvaluation(): array
    {
        return [
            "enrichment" => [
                "ecommerce" => [
                    "en_US" => [
                        "rate" => "C",
                        "recommendations" => [
                            [
                                "criterion" => "completeness_of_non_required_attributes",
                                "attributes" => [
                                    "title", "meta_title",
                                ]
                            ],
                            [
                                "criterion" => "completeness_of_required_attributes",
                                "attributes" => []
                            ],
                        ],
                        "rates" => [
                            [
                                "criterion" => "completeness_of_non_required_attributes",
                                "rate" => 50,
                                "letterRate" => "E",
                            ],
                            [
                                "criterion" => "completeness_of_required_attributes",
                                "rate" => 100,
                                "letterRate" => "A",
                            ]
                        ],
                    ],
                    "fr_FR" => [
                        "rate" => null,
                        "recommendations" => [
                            [
                                "criterion" => "completeness_of_non_required_attributes",
                                "attributes" => []
                            ],
                            [
                                "criterion" => "completeness_of_required_attributes",
                                "attributes" => []
                            ],
                        ],
                        "rates" => [
                            [
                                "criterion" => "completeness_of_non_required_attributes",
                                "rate" => null,
                                "letterRate" => null,
                            ],
                            [
                                "criterion" => "completeness_of_required_attributes",
                                "rate" => null,
                                "letterRate" => null,
                            ]
                        ],
                    ],
                ],
                "mobile" => [
                    "en_US" => [
                        "rate" => null,
                        "recommendations" => [
                            [
                                "criterion" => "completeness_of_non_required_attributes",
                                "attributes" => []
                            ],
                            [
                                "criterion" => "completeness_of_required_attributes",
                                "attributes" => []
                            ],
                        ],
                        "rates" => [
                            [
                                "criterion" => "completeness_of_non_required_attributes",
                                "rate" => null,
                                "letterRate" => null,
                            ],
                            [
                                "criterion" => "completeness_of_required_attributes",
                                "rate" => null,
                                "letterRate" => null,
                            ]
                        ],
                    ],
                ],
            ],
            "consistency" => [
                "ecommerce" => [
                    "en_US" => [
                        "rate" => "B",
                        "recommendations" => [
                            [
                                "criterion" => "consistency_spelling",
                                "attributes" => [
                                    "description",
                                ]
                            ],
                            [
                                "criterion" => "consistency_text_title_formatting",
                                "attributes" => [
                                    "title",
                                ]
                            ],
                        ],
                        "rates" => [
                            [
                                "criterion" => "consistency_spelling",
                                "rate" => 88,
                                "letterRate" => "B",
                            ],
                            [
                                "criterion" => "consistency_text_title_formatting",
                                "rate" => 85,
                                "letterRate" => "B",
                            ],
                        ],
                    ],
                    "fr_FR" => [
                        "rate" => "D",
                        "recommendations" => [
                            [
                                "criterion" => "consistency_spelling",
                                "attributes" => [
                                    "description",
                                    "short_description",
                                ]
                            ],
                            [
                                "criterion" => "consistency_text_title_formatting",
                                "attributes" => []
                            ],
                        ],
                        "rates" => [
                            [
                                "criterion" => "consistency_spelling",
                                "rate" => 68,
                                "letterRate" => "D",
                            ],
                            [
                                "criterion" => "consistency_text_title_formatting",
                                "rate" => null,
                                "letterRate" => null,
                            ],
                        ],
                    ],
                ],
                "mobile" => [
                    "en_US" => [
                        "rate" => "E",
                        "recommendations" => [
                            [
                                "criterion" => "consistency_spelling",
                                "attributes" => []
                            ],
                            [
                                "criterion" => "consistency_text_title_formatting",
                                "attributes" => [
                                   "title", "meta_title",
                                ]
                            ],
                        ],
                        "rates" => [
                            [
                                "criterion" => "consistency_spelling",
                                "rate" => 76,
                                "letterRate" => "C",
                            ],
                            [
                                "criterion" => "consistency_text_title_formatting",
                                "rate" => 0,
                                "letterRate" => "E",
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
