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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency;

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

final class EvaluateLowerCaseWordsSpec extends ObjectBehavior
{
    public function let(GetLocalesByChannelQueryInterface $localesByChannelQuery)
    {
        $this->beConstructedWith($localesByChannelQuery);
    }

    public function it_sets_the_result_status_as_not_applicable_when_a_product_has_no_values_to_evaluate(
        GetLocalesByChannelQueryInterface $localesByChannelQuery
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

        $this->evaluate(
            new Write\CriterionEvaluation(
                new CriterionCode('criterion1'),
                new ProductId(1),
                CriterionEvaluationStatus::pending()
            ),
            new ProductValuesCollection()
        )->shouldBeLike($expectedResult);
    }

    public function it_evaluates_product_values(GetLocalesByChannelQueryInterface $localesByChannelQuery)
    {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
                'mobile' => ['en_US', 'fr_FR'],
                'print' => ['en_US', 'fr_FR'],
            ]
        ));

        $textarea1 = $this->givenAnAttributeOfTypeTextarea('textarea_1');
        $textarea2 = $this->givenAnAttributeOfTypeTextarea('textarea_2');
        $textNotToEvaluate = $this->givenAnAttributeOfTypeText('a_text');

        $textarea1Values = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => '<p><br></p>',
                'fr_FR' => '<div>Text HTML without error.</div>',
            ],
            'mobile' => [
                'en_US' => 'There is: one error',
                'fr_FR' => '<p>there is: two errors</p>',
            ],
            'print' => [
                'en_US' => null,
                'fr_FR' => null,
            ],
        ], function ($value) { return $value; });

        $textarea2Values = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => 'is there: three errors? yes.',
                'fr_FR' => 'is there: three errors? yes.',
            ],
            'mobile' => [
                'en_US' => 'four errors. is worst! than three? indeed.',
                'fr_FR' => 'five: errors. are? too: much!',
            ],
            'print' => [
                'en_US' => null,
                'fr_FR' => 'Text without error.',
            ],
        ], function ($value) { return $value; });

        $textNotToEvaluateValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => 'Whatever',
                'fr_FR' => 'Peu importe',
            ],
        ], function ($value) { return $value; });

        $productValues = (new ProductValuesCollection())
            ->add(new ProductValues($textarea1, $textarea1Values))
            ->add(new ProductValues($textarea2, $textarea2Values))
            ->add(new ProductValues($textNotToEvaluate, $textNotToEvaluateValues));

        $channelEcommerce = new ChannelCode('ecommerce');
        $channelMobile = new ChannelCode('mobile');
        $channelPrint = new ChannelCode('print');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelEcommerce, $localeEn, new Rate(28))
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelEcommerce, $localeEn, ['textarea_2' => 28])

            ->addRate($channelEcommerce, $localeFr, new Rate(64))
            ->addStatus($channelEcommerce, $localeFr, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelEcommerce, $localeFr, ['textarea_1' => 100, 'textarea_2' => 28])

            ->addRate($channelMobile, $localeEn, new Rate(40))
            ->addStatus($channelMobile, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelMobile, $localeEn, ['textarea_1' => 76, 'textarea_2' => 4])

            ->addRate($channelMobile, $localeFr, new Rate(26))
            ->addStatus($channelMobile, $localeFr, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelMobile, $localeFr, ['textarea_1' => 52, 'textarea_2' => 0])

            ->addRate($channelPrint, $localeFr, new Rate(100))
            ->addStatus($channelPrint, $localeFr, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelPrint, $localeFr, ['textarea_2' => 100])

            ->addStatus($channelPrint, $localeEn, CriterionEvaluationResultStatus::notApplicable())
        ;

        $this->evaluate(
            new Write\CriterionEvaluation(
                new CriterionCode('criterion1'),
                new ProductId(1),
                CriterionEvaluationStatus::pending()
            ),
            $productValues
        )->shouldBeLike($expectedResult);
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
