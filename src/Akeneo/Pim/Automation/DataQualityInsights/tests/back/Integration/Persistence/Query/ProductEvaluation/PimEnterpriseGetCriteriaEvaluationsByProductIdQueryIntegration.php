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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\FamilyCriterionEvaluationRepository;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

final class PimEnterpriseGetCriteriaEvaluationsByProductIdQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_retrieves_the_attribute_spelling_result_of_a_product_from_its_family()
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $familyId = new FamilyId($this->createFamily('a_family')->getId());
        $productId = new ProductId($this->createProduct('ziggy', ['family' => 'a_family'])->getId());
        $this->createProduct('whatever', ['family' => 'a_family']);

        $productEvaluatedAt = new \DateTimeImmutable('2020-12-04 14:03:34');
        $this->updateProductEvaluationsAt($productId->toInt(), CriterionEvaluationStatus::DONE, $productEvaluatedAt);

        $familyAttributeSpellingEvaluation = $this->givenAFamilyAttributeSpellingEvaluation($familyId);

        $expectedAttributeSpellingEvaluation = new Read\CriterionEvaluation(
            $familyAttributeSpellingEvaluation->getCriterionCode(),
            $productId,
            $productEvaluatedAt,
            CriterionEvaluationStatus::done(),
            new Read\CriterionEvaluationResult(
                (new ChannelLocaleRateCollection())->addRate(
                    $channel,
                    $locale,
                    $familyAttributeSpellingEvaluation->getResult()->getRates()->getByChannelAndLocale($channel, $locale)
                ),
                $familyAttributeSpellingEvaluation->getResult()->getStatus(),
                $familyAttributeSpellingEvaluation->getResult()->getDataToArray()
            )
        );

        $productEvaluations = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_criteria_evaluations')->execute($productId);

        $this->assertReadCriterionEvaluationEquals(
            $expectedAttributeSpellingEvaluation,
            $productEvaluations->get($familyAttributeSpellingEvaluation->getCriterionCode())
        );
    }

    private function givenAFamilyAttributeSpellingEvaluation(FamilyId $familyId): Write\FamilyCriterionEvaluation
    {
        $attributeSpellingEvaluationResult = (new Write\CriterionEvaluationResult())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())
            ->addRateByAttributes(new ChannelCode('ecommerce'), new LocaleCode('en_US'), ['name' => 100, 'title' => 0])
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(75));

        $familyAttributeSpellingEvaluation = new Write\FamilyCriterionEvaluation(
            $familyId,
            new CriterionCode(EvaluateAttributeSpelling::CRITERION_CODE),
            new \DateTimeImmutable('2020-11-01 12:43:32'),
            $attributeSpellingEvaluationResult
        );

        $this->get(FamilyCriterionEvaluationRepository::class)->save($familyAttributeSpellingEvaluation);

        return $familyAttributeSpellingEvaluation;
    }

    private function assertReadCriterionEvaluationEquals(Read\CriterionEvaluation $expectedCriterionEvaluation, Read\CriterionEvaluation $criterionEvaluation): void
    {
        $this->assertEquals($expectedCriterionEvaluation->getCriterionCode(), $criterionEvaluation->getCriterionCode());
        $this->assertEquals($expectedCriterionEvaluation->getProductId(), $criterionEvaluation->getProductId());
        $this->assertEquals($expectedCriterionEvaluation->getStatus(), $criterionEvaluation->getStatus());
        $this->assertEquals($expectedCriterionEvaluation->getResult(), $criterionEvaluation->getResult());
        $this->assertEquals(
            $expectedCriterionEvaluation->getEvaluatedAt()->format(Clock::TIME_FORMAT),
            $criterionEvaluation->getEvaluatedAt()->format(Clock::TIME_FORMAT)
        );

    }
}
