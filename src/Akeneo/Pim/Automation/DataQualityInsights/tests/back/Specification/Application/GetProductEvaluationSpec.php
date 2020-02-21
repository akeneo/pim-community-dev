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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Consistency;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Enrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\ProductEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestProductEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
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
        GetLatestProductEvaluationQueryInterface $getLatestProductEvaluationQuery,
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery
    ) {
        $this->beConstructedWith($getLatestProductEvaluationQuery, $getLocalesByChannelQuery);

        $getLocalesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US', 'fr_FR'],
            'mobile' => ['en_US']
        ]));
    }

    public function it_gets_the_evaluations_of_a_product(
        GetLatestProductEvaluationQueryInterface $getLatestProductEvaluationQuery
    ) {
        $productId = new ProductId(2000);

        $productEvaluationReadModel = $this->givenAProductEvaluation($productId);
        $expectedEvaluation = $this->getExpectedProductEvaluation();

        $getLatestProductEvaluationQuery->execute($productId)->willReturn($productEvaluationReadModel);

        $this->get($productId)->shouldBeLike($expectedEvaluation);
    }

    private function generateCriterionEvaluation(ProductId $productId, string $code, string $status, ChannelLocaleRateCollection $resultRates, CriterionEvaluationResultStatusCollection $resultStatusCollection, array $resultData)
    {
        return new CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode($code),
            $productId,
            new \DateTimeImmutable(),
            new CriterionEvaluationStatus($status),
            new CriterionEvaluationResult($resultRates, $resultStatusCollection, $resultData),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );
    }

    private function givenAProductEvaluation(ProductId $productId): ProductEvaluation
    {
        $axesEvaluations = (new AxisEvaluationCollection())
            ->add($this->givenAnEnrichmentEvaluation($productId))
            ->add($this->givenAConsistencyEvaluation($productId))
        ;

        return new ProductEvaluation($productId, $axesEvaluations);
    }

    private function givenAnEnrichmentEvaluation(ProductId $productId): AxisEvaluation
    {
        $enrichment = new Enrichment();
        $channelCodeEcommerce = new ChannelCode('ecommerce');
        $localeCodeEn = new LocaleCode('en_US');

        $enrichmentRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(75));

        $completenessOfNonRequiredAttributesRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(50));

        $completenessOfNonRequiredAttributesStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done());

        $completenessOfNonRequiredAttributesData = [
            "attributes" => [
                "ecommerce" => [
                    "en_US" => ["title", "meta_title"]
                ]
            ]
        ];

        $completenessOfRequiredAttributesRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(100));

        $completenessOfRequiredAttributesStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done());

        $completenessOfRequiredAttributesData = [
            "attributes" => [
                "ecommerce" => []
            ]
        ];

        $enrichmentCriteriaEvaluations = (new CriterionEvaluationCollection())
            ->add($this->generateCriterionEvaluation(
                $productId,
                EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE,
                CriterionEvaluationStatus::DONE,
                $completenessOfNonRequiredAttributesRates,
                $completenessOfNonRequiredAttributesStatus,
                $completenessOfNonRequiredAttributesData
            ))
            ->add($this->generateCriterionEvaluation(
                $productId,
                EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
                CriterionEvaluationStatus::DONE,
                $completenessOfRequiredAttributesRates,
                $completenessOfRequiredAttributesStatus,
                $completenessOfRequiredAttributesData
            ))
        ;

        return new AxisEvaluation($enrichment->getCode(), $enrichmentRates, $enrichmentCriteriaEvaluations);
    }

    public function givenAConsistencyEvaluation(ProductId $productId): AxisEvaluation
    {
        $consistency = new Consistency();
        $channelCodeEcommerce = new ChannelCode('ecommerce');
        $channelCodeMobile = new ChannelCode('mobile');
        $localeCodeEn = new LocaleCode('en_US');
        $localeCodeFr = new LocaleCode('fr_FR');


        $consistencyRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(86))
            ->addRate($channelCodeEcommerce, $localeCodeFr, new Rate(68))
            ->addRate($channelCodeMobile, $localeCodeEn, new Rate(38))
        ;

        $evaluateSpellingRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(88))
            ->addRate($channelCodeEcommerce, $localeCodeFr, new Rate(68))
            ->addRate($channelCodeMobile, $localeCodeEn, new Rate(76))
        ;
        $evaluateSpellingStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done())
            ->add($channelCodeEcommerce, $localeCodeFr, CriterionEvaluationResultStatus::done())
            ->add($channelCodeMobile, $localeCodeEn, CriterionEvaluationResultStatus::done())
        ;
        $evaluateSpellingData = [
            "attributes" => [
                "ecommerce" => [
                    "en_US" => ["description"],
                    "fr_FR" => ["description", "short_description"],
                ]
            ]
        ];

        $evaluateTitleFormattingRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(84))
            ->addRate($channelCodeMobile, $localeCodeEn, new Rate(0))
        ;
        $evaluateTitleFormattingStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done())
            ->add($channelCodeEcommerce, $localeCodeFr, CriterionEvaluationResultStatus::notApplicable())
            ->add($channelCodeMobile, $localeCodeEn, CriterionEvaluationResultStatus::done())
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

        $consistencyCriteriaEvaluations = (new CriterionEvaluationCollection())
            ->add($this->generateCriterionEvaluation(
                $productId,
                EvaluateSpelling::CRITERION_CODE,
                CriterionEvaluationStatus::DONE,
                $evaluateSpellingRates,
                $evaluateSpellingStatus,
                $evaluateSpellingData
            ))
            ->add($this->generateCriterionEvaluation(
                $productId,
                EvaluateTitleFormatting::CRITERION_CODE,
                CriterionEvaluationStatus::DONE,
                $evaluateTitleFormattingRates,
                $evaluateTitleFormattingStatus,
                $evaluateTitleFormattingData
            ))
        ;

        return new AxisEvaluation($consistency->getCode(), $consistencyRates, $consistencyCriteriaEvaluations);
    }

    private function getExpectedProductEvaluation(): array
    {
        return [
            "enrichment" => [
                "ecommerce" => [
                    "en_US" => [
                        "rate" => [
                            "value" => 75,
                            "rank" => "C",
                        ],
                        "criteria" => [
                            [
                                "code" => "completeness_of_non_required_attributes",
                                "rate" => [
                                    "value" => 50,
                                    "rank" => "E",
                                ],
                                "improvable_attributes" => ["title", "meta_title"],
                                "status" => CriterionEvaluationResultStatus::DONE,
                            ],
                            [
                                "code" => "completeness_of_required_attributes",
                                "rate" => [
                                    "value" => 100,
                                    "rank" => "A",
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::DONE,
                            ],
                        ],
                    ],
                    "fr_FR" => [
                        "rate" => [
                            "value" => null,
                            "rank" => null,
                        ],
                        "criteria" => [
                            [
                                "code" => "completeness_of_non_required_attributes",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" => "completeness_of_required_attributes",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                        ],
                    ],
                ],
                "mobile" => [
                    "en_US" => [
                        "rate" => [
                            "value" => null,
                            "rank" => null,
                        ],
                        "criteria" => [
                            [
                                "code" => "completeness_of_non_required_attributes",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" =>"completeness_of_required_attributes",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                        ],
                    ],
                ],
            ],
            "consistency" => [
                "ecommerce" => [
                    "en_US" => [
                        "rate" => [
                            "value" => 86,
                            "rank" => "B",
                        ],
                        "criteria" => [
                            [
                                "code" =>"consistency_spelling",
                                "rate" => [
                                    "value" => 88,
                                    "rank" => "B",
                                ],
                                "improvable_attributes" => [
                                    "description",
                                ],
                                "status" => CriterionEvaluationResultStatus::DONE,
                            ],
                            [
                                "code" =>"consistency_text_title_formatting",
                                "rate" => [
                                    "value" => 84,
                                    "rank" => "B",
                                ],
                                "improvable_attributes" => [
                                    "title",
                                ],
                                "status" => CriterionEvaluationResultStatus::DONE,
                            ],
                        ],
                    ],
                    "fr_FR" => [
                        "rate" => [
                            "value" => 68,
                            "rank" => "D",
                        ],
                        "criteria" => [
                            [
                                "code" =>"consistency_spelling",
                                "rate" => [
                                    "value" => 68,
                                    "rank" => "D",
                                ],
                                "improvable_attributes" => [
                                    "description",
                                    "short_description",
                                ],
                                "status" => CriterionEvaluationResultStatus::DONE,
                            ],
                            [
                                "code" =>"consistency_text_title_formatting",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::NOT_APPLICABLE,
                            ],
                        ],
                    ],
                ],
                "mobile" => [
                    "en_US" => [
                        "rate" => [
                            "value" => 38,
                            "rank" => "E",
                        ],
                        "criteria" => [
                            [
                                "code" =>"consistency_spelling",
                                "rate" => [
                                    "value" => 76,
                                    "rank" => "C",
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::DONE,
                            ],
                            [
                                "code" =>"consistency_text_title_formatting",
                                "rate" => [
                                    "value" => 0,
                                    "rank" => "E",
                                ],
                                "improvable_attributes" => [
                                   "title", "meta_title",
                                ],
                                "status" => CriterionEvaluationResultStatus::DONE,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
