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

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\AxisRegistryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\AxisRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateImageEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Domain\Model\Axis\Consistency;
use PhpSpec\ObjectBehavior;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class GetProductEvaluationSpec extends ObjectBehavior
{
    private const CHANNELS_LOCALES = [
        'ecommerce' => ['en_US', 'fr_FR'],
        'mobile' => ['en_US']
    ];

    public function let(
        GetProductEvaluationQueryInterface $getProductEvaluationQuery,
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery,
        AxisRegistryInterface $axisRegistry,
        Axis $consistencyAxis
    ) {
        $consistencyAxis->getCriteriaCodes()->willReturn([
            new CriterionCode('consistency_spelling'),
            new CriterionCode('consistency_textarea_lowercase_words'),
            new CriterionCode('consistency_textarea_uppercase_words'),
            new CriterionCode('consistency_attribute_spelling'),
            new CriterionCode('consistency_attribute_option_spelling'),
        ]);
        $axisRegistry->get(new AxisCode(Enrichment::AXIS_CODE))->willReturn(new Enrichment());
        $axisRegistry->get(new AxisCode('consistency'))->willReturn($consistencyAxis);
        $this->beConstructedWith($getProductEvaluationQuery, $getLocalesByChannelQuery, $axisRegistry);

        $getLocalesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(self::CHANNELS_LOCALES));
    }

    public function it_gets_the_evaluations_of_a_product(
        GetProductEvaluationQueryInterface $getProductEvaluationQuery
    ) {
        $productId = new ProductId(2000);

        $productEvaluationReadModel = $this->givenAProductEvaluation($productId);
        $expectedEvaluation = $this->getExpectedProductEvaluation();

        $getProductEvaluationQuery->execute($productId)->willReturn($productEvaluationReadModel);

        $this->get($productId)->shouldBeLike($expectedEvaluation);
    }

    public function it_returns_default_values_if_the_product_has_no_evaluation(
        GetProductEvaluationQueryInterface $getProductEvaluationQuery
    ) {
        $productId = new ProductId(42);
        $productEvaluation = $this->givenAnEmptyProductEvaluation($productId);
        $getProductEvaluationQuery->execute($productId)->willReturn($productEvaluation);

        $expectedEvaluation = $this->getExpectedEmptyProductEvaluation();

        $this->get($productId)->shouldBeLike($expectedEvaluation);
    }

    public function it_handle_deprecated_improvable_attribute_structure(
        GetProductEvaluationQueryInterface $getProductEvaluationQuery
    ) {
        $productId = new ProductId(39);
        $productEvaluation = $this->givenADeprecatedProductEvaluation($productId);
        $getProductEvaluationQuery->execute($productId)->willReturn($productEvaluation);

        $expectedEvaluation = [
            "consistency" => [
                "ecommerce" => [
                    "en_US" => [
                        "rate" => [
                            "value" => 50,
                            "rank" => "E",
                        ],
                        "criteria" => [
                            [
                                "code" =>"consistency_spelling",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" => "consistency_textarea_lowercase_words",
                                "rate" => [
                                    "value" => 50,
                                    "rank" => "E",
                                ],
                                "improvable_attributes" => ["short_description", "long_description"],
                                "status" => CriterionEvaluationResultStatus::DONE,
                            ],
                            [
                                "code" =>"consistency_textarea_uppercase_words",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" =>"consistency_attribute_spelling",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" =>"consistency_attribute_option_spelling",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
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
                                "code" =>"consistency_spelling",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" => "consistency_textarea_lowercase_words",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" =>"consistency_textarea_uppercase_words",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" =>"consistency_attribute_spelling",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" =>"consistency_attribute_option_spelling",
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
                                "code" =>"consistency_spelling",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" => "consistency_textarea_lowercase_words",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" =>"consistency_textarea_uppercase_words",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" =>"consistency_attribute_spelling",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" =>"consistency_attribute_option_spelling",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                        ],
                    ],
                ]
            ]
        ];

        $this->get($productId)->shouldBeLike($expectedEvaluation);
    }

    private function generateCriterionEvaluation(ProductId $productId, string $code, string $status, ChannelLocaleRateCollection $resultRates, CriterionEvaluationResultStatusCollection $resultStatusCollection, array $resultData)
    {
        return new CriterionEvaluation(
            new CriterionCode($code),
            $productId,
            new \DateTimeImmutable(),
            new CriterionEvaluationStatus($status),
            new CriterionEvaluationResult($resultRates, $resultStatusCollection, $resultData)
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
            "attributes_with_rates" => [
                "ecommerce" => [
                    "en_US" => ["title" => 50, "meta_title" => 50]
                ]
            ]
        ];

        $completenessOfRequiredAttributesRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(100));

        $completenessOfRequiredAttributesStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done());

        $completenessOfRequiredAttributesData = [
            "attributes_with_rates" => [
                "ecommerce" => ["long_description" => 100]
            ]
        ];

        $imageEnrichmentRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(100));

        $imageEnrichmentStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done());

        $imageEnrichmentAttributesData = [
            "attributes_with_rates" => [
                "ecommerce" => ["picture" => 100]
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
            ->add($this->generateCriterionEvaluation(
                $productId,
                EvaluateImageEnrichment::CRITERION_CODE,
                CriterionEvaluationStatus::DONE,
                $imageEnrichmentRates,
                $imageEnrichmentStatus,
                $imageEnrichmentAttributesData
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
            "attributes_with_rates" => [
                "ecommerce" => [
                    "en_US" => ["description" => 86],
                    "fr_FR" => ["description" => 68, "short_description" => 68],
                ]
            ]
        ];

        $evaluateLowercaseRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(84))
            ->addRate($channelCodeMobile, $localeCodeEn, new Rate(0))
        ;
        $evaluateLowercaseStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done())
            ->add($channelCodeEcommerce, $localeCodeFr, CriterionEvaluationResultStatus::notApplicable())
            ->add($channelCodeMobile, $localeCodeEn, CriterionEvaluationResultStatus::done())
        ;
        $evaluateLowercaseData = [
            "attributes_with_rates" => [
                "ecommerce" => [
                    "en_US" => ["title" => 84],
                ],
                "mobile" => [
                    "en_US" => ["title" => 0, "meta_title" => 0]
                ]
            ]
        ];

        $consistencyCriteriaEvaluations = (new CriterionEvaluationCollection())
            ->add($this->generateCriterionEvaluation(
                $productId,
                'consistency_spelling',
                CriterionEvaluationStatus::DONE,
                $evaluateSpellingRates,
                $evaluateSpellingStatus,
                $evaluateSpellingData
            ))
            ->add($this->generateCriterionEvaluation(
                $productId,
                'consistency_textarea_lowercase_words',
                CriterionEvaluationStatus::DONE,
                $evaluateLowercaseRates,
                $evaluateLowercaseStatus,
                $evaluateLowercaseData
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
                                "code" => "completeness_of_required_attributes",
                                "rate" => [
                                    "value" => 100,
                                    "rank" => "A",
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::DONE,
                            ],
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
                                "code" => "enrichment_image",
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
                                "code" => "completeness_of_required_attributes",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
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
                                "code" => "enrichment_image",
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
                                "code" =>"completeness_of_required_attributes",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
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
                                "code" => "enrichment_image",
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
                                "code" => 'consistency_textarea_lowercase_words',
                                "rate" => [
                                    "value" => 84,
                                    "rank" => "B",
                                ],
                                "improvable_attributes" => [
                                    "title",
                                ],
                                "status" => CriterionEvaluationResultStatus::DONE,
                            ],
                            [
                                "code" =>"consistency_textarea_uppercase_words",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" =>"consistency_attribute_spelling",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" =>"consistency_attribute_option_spelling",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
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
                                "code" => 'consistency_textarea_lowercase_words',
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::NOT_APPLICABLE,
                            ],
                            [
                                "code" =>"consistency_textarea_uppercase_words",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" =>"consistency_attribute_spelling",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" =>"consistency_attribute_option_spelling",
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
                                "code" => 'consistency_textarea_lowercase_words',
                                "rate" => [
                                    "value" => 0,
                                    "rank" => "E",
                                ],
                                "improvable_attributes" => [
                                   "title", "meta_title",
                                ],
                                "status" => CriterionEvaluationResultStatus::DONE,
                            ],
                            [
                                "code" =>"consistency_textarea_uppercase_words",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" =>"consistency_attribute_spelling",
                                "rate" => [
                                    "value" => null,
                                    "rank" => null,
                                ],
                                "improvable_attributes" => [],
                                "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                            ],
                            [
                                "code" =>"consistency_attribute_option_spelling",
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
        ];
    }

    private function givenAnEmptyProductEvaluation(ProductId $productId)
    {
        $axesRegistry = new AxisRegistry();
        $axesEvaluations = new AxisEvaluationCollection();

        foreach ($axesRegistry->all() as $axis) {
            $axesEvaluations->add(new AxisEvaluation(
                $axis->getCode(),
                new ChannelLocaleRateCollection(),
                new CriterionEvaluationCollection()
            ));
        }

        return new ProductEvaluation($productId, $axesEvaluations);
    }

    private function getExpectedEmptyProductEvaluation(): array
    {
        $productEvaluations = [];
        $axesRegistry = new AxisRegistry();

        foreach ($axesRegistry->all() as $axis) {
            $axisCode = strval($axis->getCode());
            $productEvaluations[$axisCode] = [];
            foreach (self::CHANNELS_LOCALES as $channel => $locales) {
                foreach ($locales as $locale) {
                    $productEvaluations[$axisCode][$channel][$locale]['rate'] = [
                        "value" => null,
                        "rank" => null,
                    ];
                    foreach ($axis->getCriteriaCodes() as $criterionCode) {
                        $productEvaluations[$axisCode][$channel][$locale]['criteria'][] = [
                            "code" => strval($criterionCode),
                            "rate" => [
                                "value" => null,
                                "rank" => null,
                            ],
                            "improvable_attributes" => [],
                            "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                        ];
                    }
                }
            }
        }

        return $productEvaluations;
    }

    private function givenADeprecatedProductEvaluation(ProductId $productId): ProductEvaluation
    {
        $axesEvaluations = (new AxisEvaluationCollection())
            ->add($this->givenADeprecatedConsistencyEvaluation($productId))
        ;

        return new ProductEvaluation($productId, $axesEvaluations);
    }

    private function givenADeprecatedConsistencyEvaluation(ProductId $productId): AxisEvaluation
    {
        $consistency = new Consistency();
        $channelCodeEcommerce = new ChannelCode('ecommerce');
        $localeCodeEn = new LocaleCode('en_US');


        $consistencyRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(50));

        $lowercaseWordsRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(50));

        $lowercaseWordsAttributesDataDeprecatedFormat = [
            "attributes" => [
                "ecommerce" => [
                    "en_US" => ["short_description", "long_description"]
                ]
            ]
        ];

        $lowercaseWordsStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done());

        $consistencyCriteriaEvaluations = (new CriterionEvaluationCollection())
            ->add($this->generateCriterionEvaluation(
                $productId,
                'consistency_textarea_lowercase_words',
                CriterionEvaluationStatus::DONE,
                $lowercaseWordsRates,
                $lowercaseWordsStatus,
                $lowercaseWordsAttributesDataDeprecatedFormat
            ))
        ;

        return new AxisEvaluation($consistency->getCode(), $consistencyRates, $consistencyCriteriaEvaluations);
    }
}
