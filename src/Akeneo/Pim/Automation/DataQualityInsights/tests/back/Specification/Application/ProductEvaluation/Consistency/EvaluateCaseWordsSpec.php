<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\ComputeCaseWords\ComputeCaseWordsRate;
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

final class EvaluateCaseWordsSpec extends ObjectBehavior
{
    public function let(GetLocalesByChannelQueryInterface $localesByChannelQuery)
    {
        $this->beConstructedWith($localesByChannelQuery);
    }

    public function it_sets_the_result_status_as_not_applicable_when_a_product_has_no_values_to_evaluate(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        ComputeCaseWordsRate $computeCaseWordsRate
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
                'mobile' => ['en_US'],
            ]
        ));

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::notApplicable())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), CriterionEvaluationResultStatus::notApplicable())
            ->addStatus(new ChannelCode('mobile'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::notApplicable())
        ;

        ($this(
            new Write\CriterionEvaluation(
                new CriterionCode('criterion1'),
                new ProductId(1),
                CriterionEvaluationStatus::pending()
            ),
            new ProductValuesCollection(),
            $computeCaseWordsRate
        ))->shouldBeLike($expectedResult);
    }

    public function it_sets_the_result_status_as_not_applicable_when_a_product_values_to_evaluate_is_not_in_string_format(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        ComputeCaseWordsRate $computeCaseWordsRate
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['fr_FR'],
            ]
        ));

        $textarea = $this->givenAnAttributeOfTypeTextarea('textarea');

        // Test on array value but all non-string values are not evaluated
        $textareaValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'fr_FR' => ['This is an array.']
            ],
        ], function ($value) { return $value; });

        $productValues = (new ProductValuesCollection())
            ->add(new ProductValues($textarea, $textareaValues));

        $channelEcommerce = new ChannelCode('ecommerce');
        $localeFr = new LocaleCode('fr_FR');

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addStatus($channelEcommerce, $localeFr, CriterionEvaluationResultStatus::notApplicable());

        ($this(
            new Write\CriterionEvaluation(
                new CriterionCode('criterion1'),
                new ProductId(1),
                CriterionEvaluationStatus::pending()
            ),
            $productValues,
            $computeCaseWordsRate
        ))->shouldBeLike($expectedResult);
    }

    public function it_evaluates_product_values(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        ComputeCaseWordsRate $computeCaseWordsRate
    )
    {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US'],
                'mobile' => ['en_US', 'fr_FR'],
                'print' => ['en_US', 'fr_FR'],
            ]
        ));

        $localeChannelValues1 = [
            'ecommerce' => [
                'en_US' => '<p><br></p>',
            ],
            'mobile' => [
                'en_US' => 'There is: one error',
                'fr_FR' => '<p>there is: two errors</p>',
            ],
        ];

        $localeChannelValues2 = [
            'ecommerce' => [
                'en_US' => 'is there: three errors? yes.',
            ],
            'print' => [
                'en_US' => null,
                'fr_FR' => 'Text without error.',
            ],
        ];

        $localeChannelValues3 = [
            'ecommerce' => [
                'en_US' => 'Whatever',
            ],
        ];

        $textarea1 = $this->givenAnAttributeOfTypeTextarea('textarea_1');
        $textarea2 = $this->givenAnAttributeOfTypeTextarea('textarea_2');
        $textNotToEvaluate = $this->givenAnAttributeOfTypeText('a_text');

        $textarea1Values = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData($localeChannelValues1, function ($value) { return $value; });

        $textarea2Values = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData($localeChannelValues2, function ($value) { return $value; });

        $textNotToEvaluateValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData($localeChannelValues3, function ($value) { return $value; });

        $productValues = (new ProductValuesCollection())
            ->add(new ProductValues($textarea1, $textarea1Values))
            ->add(new ProductValues($textarea2, $textarea2Values))
            ->add(new ProductValues($textNotToEvaluate, $textNotToEvaluateValues));

        $channelEcommerce = new ChannelCode('ecommerce');
        $channelMobile = new ChannelCode('mobile');
        $channelPrint = new ChannelCode('print');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $rate0 = new Rate(0);
        $rate14 = new Rate(14);
        $rate28 = new Rate(28);
        $rate52 = new Rate(52);
        $rate76 = new Rate(76);
        $rate100 = new Rate(100);

        $computeCaseWordsRate->__invoke($localeChannelValues1['ecommerce']['en_US'])->willReturn($rate0);
        $computeCaseWordsRate->__invoke($localeChannelValues2['ecommerce']['en_US'])->willReturn($rate28);

        $computeCaseWordsRate->__invoke($localeChannelValues1['mobile']['en_US'])->willReturn($rate76);

        $computeCaseWordsRate->__invoke($localeChannelValues1['mobile']['fr_FR'])->willReturn($rate52);

        $computeCaseWordsRate->__invoke($localeChannelValues2['print']['fr_FR'])->willReturn($rate100);

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelEcommerce, $localeEn, $rate14)
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelEcommerce, $localeEn, ['textarea_1' => 0, 'textarea_2' => 28])

            ->addRate($channelMobile, $localeEn, $rate76)
            ->addStatus($channelMobile, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelMobile, $localeEn, ['textarea_1' => 76])

            ->addRate($channelMobile, $localeFr, $rate52)
            ->addStatus($channelMobile, $localeFr, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelMobile, $localeFr, ['textarea_1' => 52])

            ->addRate($channelPrint, $localeFr, $rate100)
            ->addStatus($channelPrint, $localeFr, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelPrint, $localeFr, ['textarea_2' => 100])

            ->addStatus($channelPrint, $localeEn, CriterionEvaluationResultStatus::notApplicable())
        ;

        ($this(
            new Write\CriterionEvaluation(
                new CriterionCode('criterion1'),
                new ProductId(1),
                CriterionEvaluationStatus::pending()
            ),
            $productValues,
            $computeCaseWordsRate
        ))->shouldBeLike($expectedResult);
    }

    private function givenAnAttributeOfTypeText(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::text(), true);
    }

    private function givenAnAttributeOfTypeTextarea(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::textarea(), true);
    }
}
