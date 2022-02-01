<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\ComputeCaseWords\ComputeLowerCaseWordsRate;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\ComputeCaseWords\ComputeUppercaseWordsRate;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateCaseWords;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

final class EvaluateUppercaseWordsSpec extends ObjectBehavior
{
    public function let(EvaluateCaseWords $evaluateCaseWords, ComputeUppercaseWordsRate $computeUppercaseWordsRate)
    {
        $this->beConstructedWith($evaluateCaseWords, $computeUppercaseWordsRate);
    }

    public function it_calls_evaluate_method_with_upper_case_compute_for_criterion_and_product_values(
        EvaluateCaseWords $evaluateCaseWords, ComputeUppercaseWordsRate $computeUppercaseWordsRate
    ) {
        $criterionEvaluation1 = new Write\CriterionEvaluation(
            new CriterionCode('criterion1'),
            new ProductId(1),
            CriterionEvaluationStatus::pending()
        );

        $textarea = $this->givenAnAttributeOfTypeTextarea('textarea_1');

        $textareaValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => 'TEXTAREA1 TEXT',
            ],
        ], function ($value) { return $value; });

        $productValues1 = (new ProductValuesCollection())
            ->add(new ProductValues($textarea, $textareaValues));

        $channelEcommerce = new ChannelCode('ecommerce');
        $localeEn = new LocaleCode('en_US');

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelEcommerce, $localeEn, new Rate(100))
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelEcommerce, $localeEn, ['textarea_1' => 100]);

        $evaluateCaseWords->__invoke($criterionEvaluation1, $productValues1, $computeUppercaseWordsRate)->willReturn($expectedResult);

        $this->evaluate($criterionEvaluation1, $productValues1)->shouldBeLike($expectedResult);
    }

    private function givenAnAttributeOfTypeTextarea(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::textarea(), true);
    }
}
