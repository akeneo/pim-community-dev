<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductEvaluationSpec extends ObjectBehavior
{
    public function let(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery,
        CriteriaEvaluationRegistry $criteriaEvaluationRegistry
    ) {
        $this->beConstructedWith($getCriteriaEvaluationsByProductIdQuery, $getLocalesByChannelQuery, $criteriaEvaluationRegistry);
    }

    public function it_gives_the_evaluation_of_a_product(
        $getCriteriaEvaluationsByProductIdQuery,
        $criteriaEvaluationRegistry,
        $getLocalesByChannelQuery
    ) {
        $productId = new ProductId(2000);

        $getLocalesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US', 'fr_FR'],
            'mobile' => ['en_US']
        ]));

        $criteriaEvaluationRegistry->getCriterionCodes()->willReturn([
            new CriterionCode('completeness_of_required_attributes'),
            new CriterionCode('completeness_of_non_required_attributes'),
            new CriterionCode('consistency_spelling'),
        ]);

        $getCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($this->givenProductCriteriaEvaluations($productId));

        $this->get($productId)->shouldBeLike($this->getExpectedProductEvaluation());
    }

    public function it_handle_deprecated_improvable_attribute_structure(
        $getCriteriaEvaluationsByProductIdQuery,
        $criteriaEvaluationRegistry,
        $getLocalesByChannelQuery
    ) {
        $getLocalesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US'],
        ]));

        $criteriaEvaluationRegistry->getCriterionCodes()->willReturn([
            new CriterionCode('consistency_spelling'),
            new CriterionCode('consistency_textarea_lowercase_words'),
        ]);

        $productId = new ProductId(39);
        $getCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($this->givenDeprecatedCriteriaEvaluations($productId));

        $expectedEvaluation = [
            "ecommerce" => [
                "en_US" => [
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
                ],
            ],
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

    private function givenProductCriteriaEvaluations(ProductId $productId): CriterionEvaluationCollection
    {
        $channelCodeEcommerce = new ChannelCode('ecommerce');
        $channelCodeMobile = new ChannelCode('mobile');
        $localeCodeEn = new LocaleCode('en_US');

        $completenessOfRequiredAttributesRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(95))
            ->addRate($channelCodeMobile, $localeCodeEn, new Rate(70));

        $completenessOfRequiredAttributesStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done())
            ->add($channelCodeMobile, $localeCodeEn, CriterionEvaluationResultStatus::done());

        $completenessOfRequiredAttributesData = [
            "attributes_with_rates" => [
                "ecommerce" => ["en_US" => ["long_description" => 0]],
                "mobile" => ["en_US" => ["title" => 0, "name" => 0]],
            ]
        ];

        $completenessOfNonRequiredAttributesRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(70));

        $completenessOfNonRequiredAttributesStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done());

        $completenessOfNonRequiredAttributesData = [
            "attributes_with_rates" => [
                "ecommerce" => [
                    "en_US" => ["title" => 0, "meta_title" => 0]
                ]
            ]
        ];

        $evaluateSpellingRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(88))
            ->addRate($channelCodeMobile, $localeCodeEn, new Rate(100))
        ;
        $evaluateSpellingStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done())
            ->add($channelCodeMobile, $localeCodeEn, CriterionEvaluationResultStatus::done())
        ;
        $evaluateSpellingData = [
            "attributes_with_rates" => [
                "ecommerce" => [
                    "en_US" => ["description" => 86],
                ],
                "mobile" => [
                    "en_US" => [],
                ]
            ]
        ];

        return (new CriterionEvaluationCollection())
            ->add($this->generateCriterionEvaluation(
                $productId,
                'completeness_of_required_attributes',
                CriterionEvaluationStatus::DONE,
                $completenessOfRequiredAttributesRates,
                $completenessOfRequiredAttributesStatus,
                $completenessOfRequiredAttributesData
            ))
            ->add($this->generateCriterionEvaluation(
                $productId,
                'completeness_of_non_required_attributes',
                CriterionEvaluationStatus::DONE,
                $completenessOfNonRequiredAttributesRates,
                $completenessOfNonRequiredAttributesStatus,
                $completenessOfNonRequiredAttributesData
            ))
            ->add($this->generateCriterionEvaluation(
                $productId,
                'consistency_spelling',
                CriterionEvaluationStatus::DONE,
                $evaluateSpellingRates,
                $evaluateSpellingStatus,
                $evaluateSpellingData
            ));
    }

    private function getExpectedProductEvaluation(): array
    {
        return [
            "ecommerce" => [
                "en_US" => [
                    [
                        "code" => "completeness_of_required_attributes",
                        "rate" => [
                            "value" => 95,
                            "rank" => "A",
                        ],
                        "improvable_attributes" => ["long_description"],
                        "status" => CriterionEvaluationResultStatus::DONE,
                    ],
                    [
                        "code" => "completeness_of_non_required_attributes",
                        "rate" => [
                            "value" => 70,
                            "rank" => "C",
                        ],
                        "improvable_attributes" => ["title", "meta_title"],
                        "status" => CriterionEvaluationResultStatus::DONE,
                    ],
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
                ],
                "fr_FR" => [
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
                        "code" =>"consistency_spelling",
                        "rate" => [
                            "value" => null,
                            "rank" => null,
                        ],
                        "improvable_attributes" => [],
                        "status" => CriterionEvaluationResultStatus::IN_PROGRESS,
                    ],
                ],
            ],
            "mobile" => [
                "en_US" => [
                    [
                        "code" =>"completeness_of_required_attributes",
                        "rate" => [
                            "value" => 70,
                            "rank" => "C",
                        ],
                        "improvable_attributes" => ["title", "name"],
                        "status" => CriterionEvaluationResultStatus::DONE,
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
                        "code" =>"consistency_spelling",
                        "rate" => [
                            "value" => 100,
                            "rank" => "A",
                        ],
                        "improvable_attributes" => [],
                        "status" => CriterionEvaluationResultStatus::DONE,
                    ],
                ],
            ],
        ];
    }

    private function givenDeprecatedCriteriaEvaluations(ProductId $productId): CriterionEvaluationCollection
    {
        $channelCodeEcommerce = new ChannelCode('ecommerce');
        $localeCodeEn = new LocaleCode('en_US');

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

        return (new CriterionEvaluationCollection())
            ->add($this->generateCriterionEvaluation(
                $productId,
                'consistency_textarea_lowercase_words',
                CriterionEvaluationStatus::DONE,
                $lowercaseWordsRates,
                $lowercaseWordsStatus,
                $lowercaseWordsAttributesDataDeprecatedFormat
            ));
    }
}
