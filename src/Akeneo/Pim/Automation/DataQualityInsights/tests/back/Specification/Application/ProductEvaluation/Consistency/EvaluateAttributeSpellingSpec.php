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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetProductFamilyAttributeCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
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

final class EvaluateAttributeSpellingSpec extends ObjectBehavior
{
    public function let(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        GetAttributeSpellcheckQueryInterface $getAttributeSpellcheckQuery
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
                'mobile' => ['en_US'],
            ]
        ));

        $this->beConstructedWith($localesByChannelQuery, $getAttributeSpellcheckQuery);
    }

    public function it_evaluates_product_family_attribute_spelling($getAttributeSpellcheckQuery)
    {
        $attributes = [
            new AttributeCode('sku'),
            new AttributeCode('height'),
            new AttributeCode('size'),
        ];

        $getAttributeSpellcheckQuery->getByAttributeCodes($attributes)->willReturn([
            'sku' => $this->createSkuAttributeEvaluation(),
            'height' => $this->createHeightAttributeEvaluation(),
            'size' => $this->createSizeAttributeEvaluation(),
        ]);

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())
            ->addRateByAttributes(new ChannelCode('ecommerce'), new LocaleCode('en_US'), ['sku' => 100, 'height' => 100, 'size' => 0])
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(67))

            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), CriterionEvaluationResultStatus::done())
            ->addRateByAttributes(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), ['sku' => 100, 'height' => 0, 'size' => 0])
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(33))

            ->addStatus(new ChannelCode('mobile'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())
            ->addRateByAttributes(new ChannelCode('mobile'), new LocaleCode('en_US'), ['sku' => 100, 'height' => 100, 'size' => 0])
            ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(67))
        ;

        $this->byAttributeCodes($attributes)->shouldBeLike($expectedResult);
    }

    private function createHeightAttributeEvaluation()
    {
        $attributeCode = new AttributeCode('height');

        $attributeEvaluationResult = (new SpellcheckResultByLocaleCollection())
            ->add(new LocaleCode('en_US'), new SpellCheckResult(false))
            ->add(new LocaleCode('fr_FR'), new SpellCheckResult(true));

        return new AttributeSpellcheck(
            $attributeCode,
            new \DateTimeImmutable('2020-04-12 09:12:43'),
            $attributeEvaluationResult
        );
    }

    private function createSkuAttributeEvaluation()
    {
        $attributeCode = new AttributeCode('sku');

        $attributeEvaluationResult = (new SpellcheckResultByLocaleCollection())
            ->add(new LocaleCode('en_US'), new SpellCheckResult(false))
            ->add(new LocaleCode('fr_FR'), new SpellCheckResult(false));

        return new AttributeSpellcheck(
            $attributeCode,
            new \DateTimeImmutable('2020-04-12 09:12:43'),
            $attributeEvaluationResult
        );
    }

    private function createSizeAttributeEvaluation()
    {
        $attributeCode = new AttributeCode('size');

        $attributeEvaluationResult = (new SpellcheckResultByLocaleCollection())
            ->add(new LocaleCode('en_US'), new SpellCheckResult(true))
            ->add(new LocaleCode('fr_FR'), new SpellCheckResult(true));

        return new AttributeSpellcheck(
            $attributeCode,
            new \DateTimeImmutable('2020-04-12 09:12:43'),
            $attributeEvaluationResult
        );
    }
}
