<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CompletenessCalculationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompleteEvaluationWithImprovableAttributesSpec extends ObjectBehavior
{
    public function let(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        CalculateProductCompletenessInterface $calculateRequiredAttributesCompleteness,
        CalculateProductCompletenessInterface $calculateNonRequiredAttributesCompleteness
    ) {
        $this->beConstructedWith($localesByChannelQuery, $calculateRequiredAttributesCompleteness, $calculateNonRequiredAttributesCompleteness);
    }

    public function it_completes_a_product_evaluation_with_improvable_attributes(
        $localesByChannelQuery,
        $calculateRequiredAttributesCompleteness,
        $calculateNonRequiredAttributesCompleteness
    ): void {
        $productUuid = ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed'));
        $criteriaEvaluations = $this->givenProductCriteriaEvaluationsWithCompleteness($productUuid);

        $channelCodeEcommerce = new ChannelCode('ecommerce');
        $channelCodeMobile = new ChannelCode('mobile');
        $localeCodeEn = new LocaleCode('en_US');

        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US'],
            'mobile' => ['en_US'],
        ]));

        $requiredAttributesCompletenessResult = (new CompletenessCalculationResult())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(80))
            ->addMissingAttributes($channelCodeEcommerce, $localeCodeEn, ['description', 'name'])
            ->addRate($channelCodeMobile, $localeCodeEn, new Rate(100))
            ->addMissingAttributes($channelCodeMobile, $localeCodeEn, []);

        $nonRequiredAttributesCompletenessResult = (new CompletenessCalculationResult())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(75))
            ->addMissingAttributes($channelCodeEcommerce, $localeCodeEn, ['title', 'meta_title'])
            ->addRate($channelCodeMobile, $localeCodeEn, new Rate(100))
            ->addMissingAttributes($channelCodeMobile, $localeCodeEn, []);;

        $calculateRequiredAttributesCompleteness->calculate($productUuid)->willReturn($requiredAttributesCompletenessResult);
        $calculateNonRequiredAttributesCompleteness->calculate($productUuid)->willReturn($nonRequiredAttributesCompletenessResult);

        $completedCriteriaEvaluations = $this->__invoke($criteriaEvaluations);
        $completedCriteriaEvaluations->count()->shouldBe($criteriaEvaluations->count());

        $completedRequiredCompletenessEvaluation = $completedCriteriaEvaluations->get(
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE)
        );
        $requiredCompletenessEvaluation = $criteriaEvaluations->get(
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE)
        );
        $completedRequiredCompletenessEvaluation->getResult()->getData()->shouldBe([
            'total_number_of_attributes' => 12,
            'attributes_with_rates' => [
                'ecommerce' => [
                    'en_US' => ['description' => 0, 'name' => 0]
                ],
                'mobile' => ['en_US' => []],
            ]
        ]);

        $completedRequiredCompletenessEvaluation->getResult()->getRates()->toArrayInt()->shouldBe([
            'ecommerce' => [
                'en_US' => 80
            ],
            'mobile' => [
                'en_US' =>100
            ],
        ]);

        $completedRequiredCompletenessEvaluation->getProductId()->shouldBe($productUuid);
        $completedRequiredCompletenessEvaluation->getStatus()->shouldBe($requiredCompletenessEvaluation->getStatus());
        $completedRequiredCompletenessEvaluation->getEvaluatedAt()->shouldBe($requiredCompletenessEvaluation->getEvaluatedAt());

        $completedRequiredCompletenessEvaluation->getResult()->getRates()->shouldBe($requiredCompletenessEvaluation->getResult()->getRates());
        $completedRequiredCompletenessEvaluation->getResult()->getStatus()->shouldBe($requiredCompletenessEvaluation->getResult()->getStatus());

        $completedNonRequiredCompletenessEvaluation = $completedCriteriaEvaluations->get(
            new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE)
        );
        $completedNonRequiredCompletenessEvaluation->getResult()->getData()->shouldBe([
            'total_number_of_attributes' => 7,
            'attributes_with_rates' => [
                'ecommerce' => [
                    'en_US' => ['title' => 0, 'meta_title' => 0]
                ],
                'mobile' => ['en_US' => []],
            ]
        ]);
        $completedNonRequiredCompletenessEvaluation->getResult()->getRates()->toArrayInt()->shouldBe([
            'ecommerce' => [
                'en_US' => 75
            ],
            'mobile' => [
                'en_US' =>100
            ],
        ]);

        $spellingCriterionCode = new CriterionCode('consistency_spelling');
        $completedCriteriaEvaluations->get($spellingCriterionCode)->shouldBe($criteriaEvaluations->get($spellingCriterionCode));
    }

    public function it_does_nothing_when_there_is_no_criterion_to_complete(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        CalculateProductCompletenessInterface $calculateRequiredAttributesCompleteness,
        CalculateProductCompletenessInterface $calculateNonRequiredAttributesCompleteness
    ): void {
        $productUuid = ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed'));
        $criteriaEvaluations = $this->givenProductCriteriaEvaluationsWithoutCompleteness($productUuid);

        $localesByChannelQuery->getChannelLocaleCollection()->shouldNotBeCalled();
        $calculateRequiredAttributesCompleteness->calculate(Argument::any())->shouldNotBeCalled();
        $calculateNonRequiredAttributesCompleteness->calculate(Argument::any())->shouldNotBeCalled();

        $this->__invoke($criteriaEvaluations)->shouldReturn($criteriaEvaluations);
    }

    private function givenProductCriteriaEvaluationsWithCompleteness(ProductUuid $productId): CriterionEvaluationCollection
    {
        $channelCodeEcommerce = new ChannelCode('ecommerce');
        $channelCodeMobile = new ChannelCode('mobile');
        $localeCodeEn = new LocaleCode('en_US');

        $completenessOfRequiredAttributesRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(100))
            ->addRate($channelCodeMobile, $localeCodeEn, new Rate(70));

        $completenessOfRequiredAttributesStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done())
            ->add($channelCodeMobile, $localeCodeEn, CriterionEvaluationResultStatus::done());

        $completenessOfNonRequiredAttributesRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(70));

        $completenessOfNonRequiredAttributesStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done());

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
                ['total_number_of_attributes' => 12]
            ))
            ->add($this->generateCriterionEvaluation(
                $productId,
                EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE,
                CriterionEvaluationStatus::DONE,
                $completenessOfNonRequiredAttributesRates,
                $completenessOfNonRequiredAttributesStatus,
                ['total_number_of_attributes' => 7]
            ))
            ->add($this->generateCriterionEvaluation(
                $productId,
                'consistency_spelling',
                CriterionEvaluationStatus::DONE,
                $evaluateSpellingRates,
                $evaluateSpellingStatus,
                $evaluateSpellingData
            )
        );
    }

    private function givenProductCriteriaEvaluationsWithoutCompleteness(ProductUuid $productId): CriterionEvaluationCollection
    {
        $channelCodeEcommerce = new ChannelCode('ecommerce');
        $channelCodeMobile = new ChannelCode('mobile');
        $localeCodeEn = new LocaleCode('en_US');

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
                'consistency_spelling',
                CriterionEvaluationStatus::DONE,
                $evaluateSpellingRates,
                $evaluateSpellingStatus,
                $evaluateSpellingData
            )
        );
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
}
