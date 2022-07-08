<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaByFeatureRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CompleteEvaluationWithImprovableAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByEntityIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductEvaluationSpec extends ObjectBehavior
{
    public function let(
        GetCriteriaEvaluationsByEntityIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery,
        CriteriaByFeatureRegistry $criteriaRegistry,
        CompleteEvaluationWithImprovableAttributes $completeEvaluationWithImprovableAttributes
    ) {
        $this->beConstructedWith(
            $getCriteriaEvaluationsByProductIdQuery,
            $getLocalesByChannelQuery,
            $criteriaRegistry,
            $completeEvaluationWithImprovableAttributes
        );
    }

    public function it_gives_the_evaluation_of_a_product(
        $getCriteriaEvaluationsByProductIdQuery,
        $criteriaRegistry,
        $getLocalesByChannelQuery,
        $completeEvaluationWithImprovableAttributes
    ) {
        $productUuid = ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed');

        $getLocalesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US', 'fr_FR'],
            'mobile' => ['en_US']
        ]));

        $criteriaRegistry->getEnabledCriterionCodes()->willReturn([
            new CriterionCode('completeness_of_required_attributes'),
            new CriterionCode('completeness_of_non_required_attributes'),
            new CriterionCode('consistency_spelling'),
        ]);

        $criteriaEvaluations = $this->givenProductCriteriaEvaluations($productUuid);
        $getCriteriaEvaluationsByProductIdQuery->execute($productUuid)->willReturn($criteriaEvaluations);
        $completedCriteriaEvaluations = $this->givenCompletedCriteriaEvaluations($criteriaEvaluations);
        $completeEvaluationWithImprovableAttributes->__invoke($criteriaEvaluations)->willReturn($completedCriteriaEvaluations);

        $this->get($productUuid)->shouldBeLike($this->getExpectedProductEvaluation());
    }

    public function it_gives_the_evaluation_of_a_product_model(
        $getCriteriaEvaluationsByProductIdQuery,
        $criteriaRegistry,
        $getLocalesByChannelQuery,
        $completeEvaluationWithImprovableAttributes
    ) {
        $productModelId = new ProductModelId(42);

        $getLocalesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US', 'fr_FR'],
            'mobile' => ['en_US']
        ]));

        $criteriaRegistry->getEnabledCriterionCodes()->willReturn([
            new CriterionCode('completeness_of_required_attributes'),
            new CriterionCode('completeness_of_non_required_attributes'),
            new CriterionCode('consistency_spelling'),
        ]);

        $criteriaEvaluations = $this->givenProductCriteriaEvaluations($productModelId);
        $getCriteriaEvaluationsByProductIdQuery->execute($productModelId)->willReturn($criteriaEvaluations);
        $completedCriteriaEvaluations = $this->givenCompletedCriteriaEvaluations($criteriaEvaluations);
        $completeEvaluationWithImprovableAttributes->__invoke($criteriaEvaluations)->willReturn($completedCriteriaEvaluations);

        $this->get($productModelId)->shouldBeLike($this->getExpectedProductEvaluation());
    }

    public function it_handle_deprecated_improvable_attribute_structure(
        $getCriteriaEvaluationsByProductIdQuery,
        $criteriaRegistry,
        $getLocalesByChannelQuery,
        $completeEvaluationWithImprovableAttributes
    ) {
        $getLocalesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US'],
        ]));

        $criteriaRegistry->getEnabledCriterionCodes()->willReturn([
            new CriterionCode('consistency_spelling'),
            new CriterionCode('consistency_textarea_lowercase_words'),
        ]);

        $productUuid = ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed');
        $criteriaEvaluations = $this->givenDeprecatedCriteriaEvaluations($productUuid);
        $getCriteriaEvaluationsByProductIdQuery->execute($productUuid)->willReturn($criteriaEvaluations);
        $completeEvaluationWithImprovableAttributes->__invoke($criteriaEvaluations)->willReturn($criteriaEvaluations);

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

        $this->get($productUuid)->shouldBeLike($expectedEvaluation);
    }

    private function generateCriterionEvaluation(ProductUuid $productId, string $code, string $status, ChannelLocaleRateCollection $resultRates, CriterionEvaluationResultStatusCollection $resultStatusCollection, array $resultData)
    {
        return new CriterionEvaluation(
            new CriterionCode($code),
            $productId,
            new \DateTimeImmutable(),
            new CriterionEvaluationStatus($status),
            new CriterionEvaluationResult($resultRates, $resultStatusCollection, $resultData)
        );
    }

    private function givenProductCriteriaEvaluations(ProductEntityIdInterface $productId): CriterionEvaluationCollection
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

        $completenessOfRequiredAttributesData = [];

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
                EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
                CriterionEvaluationStatus::DONE,
                $completenessOfRequiredAttributesRates,
                $completenessOfRequiredAttributesStatus,
                $completenessOfRequiredAttributesData
            ))
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
                'consistency_spelling',
                CriterionEvaluationStatus::DONE,
                $evaluateSpellingRates,
                $evaluateSpellingStatus,
                $evaluateSpellingData
            ));
    }

    private function givenCompletedCriteriaEvaluations(CriterionEvaluationCollection $criteriaEvaluations): CriterionEvaluationCollection
    {
        $criterionEvaluation = $criteriaEvaluations->get(new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE));
        $evaluationResultData = $criterionEvaluation->getResult()->getData();
        $evaluationResultData['attributes_with_rates'] = [
            "ecommerce" => ["en_US" => ["long_description" => 0]],
            "mobile" => ["en_US" => ["title" => 0, "name" => 0]],
        ];

        $completedCriterionEvaluationResult = new Read\CriterionEvaluationResult(
            $criterionEvaluation->getResult()->getRates(),
            $criterionEvaluation->getResult()->getStatus(),
            $evaluationResultData
        );

        $completedCriterionEvaluation = new Read\CriterionEvaluation(
            $criterionEvaluation->getCriterionCode(),
            $criterionEvaluation->getProductId(),
            $criterionEvaluation->getEvaluatedAt(),
            $criterionEvaluation->getStatus(),
            $completedCriterionEvaluationResult
        );

        return $criteriaEvaluations->add($completedCriterionEvaluation);
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

    private function givenDeprecatedCriteriaEvaluations(ProductUuid $productUuid): CriterionEvaluationCollection
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
                $productUuid,
                'consistency_textarea_lowercase_words',
                CriterionEvaluationStatus::DONE,
                $lowercaseWordsRates,
                $lowercaseWordsStatus,
                $lowercaseWordsAttributesDataDeprecatedFormat
            ));
    }
}
