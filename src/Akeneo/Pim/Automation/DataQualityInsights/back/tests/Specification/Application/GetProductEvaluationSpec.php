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
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestCriteriaEvaluationsByProductIdQueryInterface;
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
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery
    ) {
        $this->beConstructedWith($getLatestCriteriaEvaluationsByProductIdQuery);
    }

    public function it_gets_product_evaluation_without_result(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery
    ) {
        $productId = new ProductId(1000);
        $rawEvaluation = new CriterionEvaluationCollection();

        $getLatestCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($rawEvaluation);

        $this->get($productId)->shouldBeLike([
            "enrichment" => [],
            "consistency" => [],
        ]);
    }

    public function it_gets_complete_product_evaluation(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery
    ) {
        $productId = new ProductId(2000);
        $rawEvaluation = $this->generateCompleteEvaluation($productId);
        $expectedEvaluation = $this->generateExpectedCompleteEvaluation();

        $getLatestCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($rawEvaluation);

        $this->get($productId)->shouldBeLike($expectedEvaluation);
    }

    /** Evaluation is not applicable for the Consistency Axis */
    public function it_gets_partial_product_evaluation(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery
    ) {
        $productId = new ProductId(3000);
        $rawEvaluation = $this->generatePartialEvaluation($productId);
        $expectedEvaluation = $this->generateExpectedPartialEvaluation();

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
        $channelCode = new ChannelCode('ecommerce');
        $localeCode = new LocaleCode('en_US');

        $completenessOfNonRequiredAttributesRates = new CriterionRateCollection();
        $completenessOfNonRequiredAttributesRates
            ->addRate($channelCode, $localeCode, new Rate(50))
        ;
        $completenessOfNonRequiredAttributesData = [
            "attributes" => [
                "ecommerce" => [
                    "en_US" => ["meta_title"]
                ]
            ]
        ];

        $completenessOfRequiredAttributesRates = new CriterionRateCollection();
        $completenessOfRequiredAttributesRates
            ->addRate($channelCode, $localeCode, new Rate(50))
        ;
        $completenessOfRequiredAttributesData = [
            "attributes" => [
                "ecommerce" => [
                    "en_US" => ["title"]
                ]
            ]
        ];

        $evaluateSpellingRates = new CriterionRateCollection();
        $evaluateSpellingRates
            ->addRate($channelCode, $localeCode, new Rate(88))
        ;
        $evaluateSpellingData = [
            "attributes" => [
                "ecommerce" => [
                    "en_US" => ["description", "short_description"]
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
        ;

        return $evaluation;
    }

    private function generateExpectedCompleteEvaluation(): array
    {
        return [
            "enrichment" => [
                "ecommerce" => [
                    "en_US" => [
                        "rate" => "E",
                        "recommendations" => [
                            [
                                "criterion" => "completeness_of_non_required_attributes",
                                "attributes" => [
                                    "meta_title",
                                ]
                            ],
                            [
                                "criterion" => "completeness_of_required_attributes",
                                "attributes" => [
                                    "title",
                                ]
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
                                "rate" => 50,
                                "letterRate" => "E",
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
                                    "short_description",
                                ]
                            ],
                        ],
                        "rates" => [
                            [
                                "criterion" => "consistency_spelling",
                                "rate" => 88,
                                "letterRate" => "B",
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function generatePartialEvaluation(ProductId $productId): CriterionEvaluationCollection
    {
        $channelCode = new ChannelCode('ecommerce');
        $localeCode = new LocaleCode('en_US');

        $completenessOfNonRequiredAttributesRates = new CriterionRateCollection();
        $completenessOfNonRequiredAttributesRates
            ->addRate($channelCode, $localeCode, new Rate(50))
        ;
        $completenessOfNonRequiredAttributesData = [
            "attributes" => [
                "ecommerce" => [
                    "en_US" => ["meta_title"]
                ]
            ]
        ];

        $completenessOfRequiredAttributesRates = new CriterionRateCollection();
        $completenessOfRequiredAttributesRates
            ->addRate($channelCode, $localeCode, new Rate(50))
        ;
        $completenessOfRequiredAttributesData = [
            "attributes" => [
                "ecommerce" => [
                    "en_US" => ["title"]
                ]
            ]
        ];

        $evaluateSpellingRates = new CriterionRateCollection();
        $evaluateSpellingData = [];

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
        ;

        return $evaluation;
    }

    private function generateExpectedPartialEvaluation(): array
    {
        return [
            "enrichment" => [
                "ecommerce" => [
                    "en_US" => [
                        "rate" => "E",
                        "recommendations" => [
                            [
                                "criterion" => "completeness_of_non_required_attributes",
                                "attributes" => [
                                    "meta_title",
                                ]
                            ],
                            [
                                "criterion" => "completeness_of_required_attributes",
                                "attributes" => [
                                    "title",
                                ]
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
                                "rate" => 50,
                                "letterRate" => "E",
                            ]
                        ],
                    ],
                ],
            ],
            "consistency" => [],
        ];
    }
}
