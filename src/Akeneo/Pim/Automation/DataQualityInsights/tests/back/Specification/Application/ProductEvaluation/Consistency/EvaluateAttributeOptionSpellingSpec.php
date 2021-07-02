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

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeOptionSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use PhpSpec\ObjectBehavior;

class EvaluateAttributeOptionSpellingSpec extends ObjectBehavior
{
    public function let(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        GetAttributeOptionSpellcheckQueryInterface $getAttributeOptionSpellcheckQuery
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(
            new ChannelLocaleCollection([
                'ecommerce' => ['en_US', 'fr_FR'],
                'print' => ['en_US', 'fr_FR'],
            ])
        );
        $this->beConstructedWith($localesByChannelQuery, $getAttributeOptionSpellcheckQuery);
    }

    public function it_evaluates_attribute_option_spelling(
        GetAttributeOptionSpellcheckQueryInterface $getAttributeOptionSpellcheckQuery
    ) {
        $productId = new ProductId(1);
        $criterionEvaluation = new Write\CriterionEvaluation(
            new CriterionCode(EvaluateAttributeOptionSpelling::CRITERION_CODE),
            $productId,
            CriterionEvaluationStatus::pending()
        );

        $simpleSelectSizeValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => 'xl',
                'fr_FR' => '',
            ],
            'print' => [
                'en_US' => 'xl',
                'fr_FR' => '',
            ],
        ], function ($value) { return $value; });

        $multiSelectColorValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => ['blue', 'red', 'grey', 'black'],
                'fr_FR' => [],
            ],
            'print' => [
                'en_US' => ['blue', 'white'],
                'fr_FR' => ['blue'],
            ],
        ], function ($value) { return $value; });

        $invalidSimpleSelectHeightValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => true,
            ],
        ], function ($value) { return $value; });

        $productSimpleSelectValues = new ProductValues(
            new Attribute(new AttributeCode('size'), AttributeType::simpleSelect(), true),
            $simpleSelectSizeValues
        );
        $productMultiSelectValues = new ProductValues(
            new Attribute(new AttributeCode('color'), AttributeType::multiSelect(), true),
            $multiSelectColorValues
        );
        $invalidProductSimpleSelectValues = new ProductValues(
            new Attribute(new AttributeCode('height'), AttributeType::simpleSelect(), true),
            $invalidSimpleSelectHeightValues
        );

        $productValues = (new ProductValuesCollection())
            ->add($productSimpleSelectValues)
            ->add($invalidProductSimpleSelectValues)
            ->add($productMultiSelectValues);

        $getAttributeOptionSpellcheckQuery
            ->getByAttributeAndOptionCodes(new AttributeCode('color'), ['blue', 'red', 'grey', 'black', 'white'])
            ->willReturn([
                'blue' => new AttributeOptionSpellcheck(
                    new AttributeOptionCode(new AttributeCode('color'), 'blue'),
                    new \DateTimeImmutable(),
                    (new SpellcheckResultByLocaleCollection())
                        ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                        ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove())
                ),
                'red' => new AttributeOptionSpellcheck(
                    new AttributeOptionCode(new AttributeCode('color'), 'red'),
                    new \DateTimeImmutable(),
                    (new SpellcheckResultByLocaleCollection())
                        ->add(new LocaleCode('en_US'), SpellCheckResult::toImprove())
                        ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove())
                ),
                'grey' => new AttributeOptionSpellcheck(
                    new AttributeOptionCode(new AttributeCode('color'), 'grey'),
                    new \DateTimeImmutable(),
                    (new SpellcheckResultByLocaleCollection())
                        ->add(new LocaleCode('en_US'), SpellCheckResult::toImprove())
                        ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove())
                ),
                'white' => new AttributeOptionSpellcheck(
                    new AttributeOptionCode(new AttributeCode('color'), 'white'),
                    new \DateTimeImmutable(),
                    (new SpellcheckResultByLocaleCollection())
                        ->add(new LocaleCode('en_US'), SpellCheckResult::toImprove())
                        ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove())
                ),
            ]);

        $getAttributeOptionSpellcheckQuery
            ->getByAttributeAndOptionCodes(new AttributeCode('size'), ['xl'])
            ->willReturn([
                'xl' => new AttributeOptionSpellcheck(
                    new AttributeOptionCode(new AttributeCode('size'), 'xl'),
                    new \DateTimeImmutable(),
                    (new SpellcheckResultByLocaleCollection())
                        ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                        ->add(new LocaleCode('fr_FR'), SpellCheckResult::good())
                ),
            ]);

        $getAttributeOptionSpellcheckQuery
            ->getByAttributeAndOptionCodes(new AttributeCode('height'), true)
            ->shouldNotBeCalled();

        $channelEcommerce = new ChannelCode('ecommerce');
        $channelPrint = new ChannelCode('print');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $expectedEvaluationResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelEcommerce, $localeEn, new Rate(66))
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelEcommerce, $localeEn, ['size' => 100, 'color' => 33])

            ->addStatus($channelEcommerce, $localeFr, CriterionEvaluationResultStatus::notApplicable())

            ->addRate($channelPrint, $localeEn, new Rate(75))
            ->addStatus($channelPrint, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelPrint, $localeEn, ['size' => 100, 'color' => 50])

            ->addRate($channelPrint, $localeFr, new Rate(0))
            ->addStatus($channelPrint, $localeFr, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelPrint, $localeFr, ['color' => 0])
        ;

        $result = $this->evaluate($criterionEvaluation, $productValues);
        $result->shouldBeLike($expectedEvaluationResult);
    }
}
