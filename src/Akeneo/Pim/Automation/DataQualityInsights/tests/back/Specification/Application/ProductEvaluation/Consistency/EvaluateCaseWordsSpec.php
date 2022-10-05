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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Subject;
use Ramsey\Uuid\Uuid;

final class EvaluateCaseWordsSpec extends ObjectBehavior
{
    public function let(GetLocalesByChannelQueryInterface $localesByChannelQuery)
    {
        $this->beConstructedWith($localesByChannelQuery);
    }

    public function it_evaluates_product_values(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        ComputeCaseWordsRate $computeCaseWordsRate,
    )
    {
        $attributeName = 'my_attribute';
        $attributeType = AttributeType::textarea();
        $expectedRate = 10;

        $actualResult = $this->evaluate(
            $localesByChannelQuery,
            $computeCaseWordsRate,
            $attributeName,
            $attributeType,
            'DUMMY',
            new Rate($expectedRate)
        );

        $actualResult->shouldBeLike((new CriterionEvaluationResult())
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate($expectedRate))
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())
            ->addRateByAttributes(new ChannelCode('ecommerce'), new LocaleCode('en_US'), [$attributeName => $expectedRate]));
    }

    public function it_does_not_evaluate_null_value(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        ComputeCaseWordsRate $computeCaseWordsRate,
    )
    {
        $attributeName = 'my_attribute';
        $attributeType = AttributeType::textarea();

        $actualResult = $this->evaluate(
            $localesByChannelQuery,
            $computeCaseWordsRate,
            $attributeName,
            $attributeType,
            null,
            null,
        );

        $actualResult->shouldBeLike((new CriterionEvaluationResult())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::notApplicable()));
    }

    public function it_does_not_evaluate_text_attribute(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        ComputeCaseWordsRate $computeCaseWordsRate,
    )
    {
        $attributeName = 'my_attribute';
        $attributeType = AttributeType::text();

        $actualResult = $this->evaluate(
            $localesByChannelQuery,
            $computeCaseWordsRate,
            $attributeName,
            $attributeType,
            'DUMMY',
            null,
        );

        $actualResult->shouldBeLike((new CriterionEvaluationResult())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::notApplicable()));
    }

    public function it_does_not_evaluate_non_string_value(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        ComputeCaseWordsRate $computeCaseWordsRate
    ) {
        $attributeName = 'my_attribute';
        $attributeType = AttributeType::textarea();

        $actualResult = $this->evaluate(
            $localesByChannelQuery,
            $computeCaseWordsRate,
            $attributeName,
            $attributeType,
            ['This is an array, not a string.'],
            null
        );

        $actualResult->shouldBeLike((new CriterionEvaluationResult())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::notApplicable()));
    }

    private function evaluate(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        ComputeCaseWordsRate $computeCaseWordsRate,
        string $attributeName,
        AttributeType $attributeType,
        mixed $value,
        Rate $expectedRate
    ): Subject
    {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US'],
            ]
        ));

        $localeChannelValues1 = [
            'ecommerce' => [
                'en_US' => $value,
            ],
        ];

        $textarea1 = $this->givenAnAttribute($attributeName, $attributeType);

        $textarea1Values = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData($localeChannelValues1, function ($value) { return $value; });

        $productValues = (new ProductValuesCollection())
            ->add(new ProductValues($textarea1, $textarea1Values));

        $computeCaseWordsRate->__invoke($localeChannelValues1['ecommerce']['en_US'])->willReturn($expectedRate);

        return $this(
            new CriterionEvaluation(
                new CriterionCode('criterion1'),
                ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
                CriterionEvaluationStatus::pending()
            ),
            $productValues,
            $computeCaseWordsRate
        );
    }

    public function it_does_not_evaluate_when_no_values(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        ComputeCaseWordsRate $computeCaseWordsRate
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US'],
            ]
        ));

        $expectedResult = (new CriterionEvaluationResult())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::notApplicable())
        ;

        ($this(
            new CriterionEvaluation(
                new CriterionCode('criterion1'),
                ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
                CriterionEvaluationStatus::pending()
            ),
            new ProductValuesCollection(),
            $computeCaseWordsRate
        ))->shouldBeLike($expectedResult);
    }

    public function it_evaluates_rate_average_by_channel_locale(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        ComputeCaseWordsRate $computeCaseWordsRate
    )
    {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US'],
            ]
        ));

        $localeChannelValues1 = [
            'ecommerce' => [
                'en_US' => 'DUMMY1',
            ],
        ];

        $localeChannelValues2 = [
            'ecommerce' => [
                'en_US' => 'DUMMY2',
            ],
        ];

        $textarea1 = $this->givenAnAttribute('textarea_1', AttributeType::textarea());
        $textarea2 = $this->givenAnAttribute('textarea_2', AttributeType::textarea());

        $textarea1Values = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData($localeChannelValues1, function ($value) { return $value; });
        $textarea2Values = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData($localeChannelValues2, function ($value) { return $value; });

        $productValues = (new ProductValuesCollection())
            ->add(new ProductValues($textarea1, $textarea1Values))
            ->add(new ProductValues($textarea2, $textarea2Values));

        $channelEcommerce = new ChannelCode('ecommerce');
        $localeEn = new LocaleCode('en_US');

        $lowRate = 0;
        $avgRate = 14;
        $highRate = 28;

        $computeCaseWordsRate->__invoke($localeChannelValues1['ecommerce']['en_US'])->willReturn(new Rate($lowRate));
        $computeCaseWordsRate->__invoke($localeChannelValues2['ecommerce']['en_US'])->willReturn(new Rate($highRate));

        $expectedResult = (new CriterionEvaluationResult())
            ->addRate($channelEcommerce, $localeEn, new Rate($avgRate))
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelEcommerce, $localeEn, ['textarea_1' => $lowRate, 'textarea_2' => $highRate]);

        $result = $this(
            new CriterionEvaluation(
                new CriterionCode('criterion1'),
                ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
                CriterionEvaluationStatus::pending()
            ),
            $productValues,
            $computeCaseWordsRate
        );

        $result->shouldBeLike($expectedResult);
    }

    private function givenAnAttribute(string $code, AttributeType $attributeType): Attribute
    {
        return new Attribute(new AttributeCode($code), $attributeType, true);
    }
}
