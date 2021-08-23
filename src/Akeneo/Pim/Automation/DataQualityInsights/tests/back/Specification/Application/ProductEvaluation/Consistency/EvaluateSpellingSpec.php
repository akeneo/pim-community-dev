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

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\FilterProductValuesForSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\MultipleTextsChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\TextCheckFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
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
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class EvaluateSpellingSpec extends ObjectBehavior
{
    public function let(
        MultipleTextsChecker $textChecker,
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        SupportedLocaleValidator $supportedLocaleValidator,
        FilterProductValuesForSpelling $filterProductValuesForSpelling,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($textChecker, $localesByChannelQuery, $supportedLocaleValidator, $filterProductValuesForSpelling, $logger);
    }

    public function it_evaluates_rates_for_textarea_and_text_values(
        $textChecker,
        $localesByChannelQuery,
        $supportedLocaleValidator,
        $filterProductValuesForSpelling,
        TextCheckResultCollection $textCheckResultTextareaEcommerceEn,
        TextCheckResultCollection $textCheckResultTextareaPrintEn,
        TextCheckResultCollection $textCheckResultTextareaEcommerceFr,
        TextCheckResultCollection $textCheckResultTextEcommerceEn,
        TextCheckResultCollection $textCheckResultTextEcommerceFR,
        TextCheckResultCollection $textCheckResultTextPrintEn
    ) {
        $productId = new ProductId(1);
        $criterionEvaluation = new Write\CriterionEvaluation(
            new CriterionCode(EvaluateSpelling::CRITERION_CODE),
            $productId,
            CriterionEvaluationStatus::pending()
        );

        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US', 'fr_FR', 'it_IT'],
            'print' => ['en_US', 'fr_FR'],
        ]));

        $attributeText = $this->givenALocalizableAttributeOfTypeText('a_text');
        $attributeTextarea = $this->givenALocalizableAttributeOfTypeTextarea('a_textarea');

        $textareaValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => '<p>Typos hapen. </p>',
                'fr_FR' => '<p>Les fautes de frappe arrivent. </p>',
                'it_IT' => '<p>I refusi accadono. </p>',
            ],
            'print' => [
                'en_US' => '<p>Typos happen. </p>',
                'fr_FR' => '',
                'it_IT' => 'I refusi accadono.',
            ],
        ], function ($value) { return $value; });

        $textValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => 'Typos happen.',
                'fr_FR' => 'Les fautes de frappe arrivent',
                'it_IT' => 'I refusi accadono.',
            ],
            'print' => [
                'en_US' => 'Typos hapen.',
                'fr_FR' => null,
                'it_IT' => 'I refusi accadono.',
            ],
        ], function ($value) { return $value; });

        $productTextValues = new ProductValues($attributeText, $textValues);
        $productTextareaValues = new ProductValues($attributeTextarea, $textareaValues);

        $productValues = (new ProductValuesCollection())
            ->add($productTextValues)
            ->add($productTextareaValues);

        $filterProductValuesForSpelling->getFilteredProductValues($productValues)->willReturn([$productTextValues, $productTextareaValues]);

        $channelEcommerce = new ChannelCode('ecommerce');
        $channelPrint = new ChannelCode('print');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');
        $localeIt = new LocaleCode('it_IT');

        $supportedLocaleValidator->isSupported($localeEn)->willReturn(true);
        $supportedLocaleValidator->isSupported($localeFr)->willReturn(true);
        $supportedLocaleValidator->isSupported($localeIt)->willReturn(false);

        $textCheckResultTextareaEcommerceEn->count()->willReturn(1);
        $textCheckResultTextareaPrintEn->count()->willReturn(0);
        $textCheckResultTextareaEcommerceFr->count()->willReturn(0);
        $textCheckResultTextEcommerceEn->count()->willReturn(0);
        $textCheckResultTextEcommerceFR->count()->willReturn(0);
        $textCheckResultTextPrintEn->count()->willReturn(1);

        $textChecker
            ->check(['a_text' => 'Typos happen.', 'a_textarea' => '<p>Typos hapen. </p>'], $localeEn)
            ->willReturn(['a_text' => $textCheckResultTextEcommerceEn, 'a_textarea' => $textCheckResultTextareaEcommerceEn]);

        $textChecker
            ->check(['a_text' => 'Les fautes de frappe arrivent', 'a_textarea' => '<p>Les fautes de frappe arrivent. </p>'], $localeFr)
            ->willReturn(['a_text' => $textCheckResultTextEcommerceFR, 'a_textarea' => $textCheckResultTextareaEcommerceFr]);

        $textChecker
            ->check(['a_text' => 'Typos hapen.', 'a_textarea' => '<p>Typos happen. </p>'], $localeEn)
            ->willReturn(['a_text' => $textCheckResultTextPrintEn, 'a_textarea' => $textCheckResultTextareaPrintEn]);

        $expectedEvaluationResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelEcommerce, $localeEn, new Rate(94))
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelEcommerce, $localeEn, ['a_text' => 100, 'a_textarea' => 88])

            ->addRate($channelEcommerce, $localeFr, new Rate(100))
            ->addStatus($channelEcommerce, $localeFr, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelEcommerce, $localeFr, ['a_text' => 100, 'a_textarea' => 100])

            ->addStatus($channelEcommerce, $localeIt, CriterionEvaluationResultStatus::notApplicable())

            ->addRate($channelPrint, $localeEn, new Rate(88))
            ->addStatus($channelPrint, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelPrint, $localeEn, ['a_text' => 76, 'a_textarea' => 100])

            ->addStatus($channelPrint, $localeFr, CriterionEvaluationResultStatus::notApplicable())
        ;

        $this->evaluate($criterionEvaluation, $productValues)->shouldBeLike($expectedEvaluationResult);
    }

    public function it_sets_status_in_error_when_the_text_checking_fails(
        MultipleTextsChecker $textChecker,
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        SupportedLocaleValidator $supportedLocaleValidator,
        TextCheckResultCollection $textCheckResultTextareaPrintEn,
        FilterProductValuesForSpelling $filterProductValuesForSpelling
    ) {
        $productId = new ProductId(42);
        $criterionEvaluation = new Write\CriterionEvaluation(
            new CriterionCode(EvaluateSpelling::CRITERION_CODE),
            $productId,
            CriterionEvaluationStatus::pending()
        );

        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'print' => ['en_US', 'fr_FR'],
        ]));

        $attributeTextarea = $this->givenALocalizableAttributeOfTypeTextarea('a_textarea');
        $textareaValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'print' => [
                'en_US' => 'Success',
                'fr_FR' => 'Fail',
            ],
        ], function ($value) { return $value; });

        $productTextValues = new ProductValues($attributeTextarea, $textareaValues);
        $productValues = (new ProductValuesCollection())->add($productTextValues);

        $filterProductValuesForSpelling->getFilteredProductValues($productValues)->willReturn([$productTextValues]);

        $channelPrint = new ChannelCode('print');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $supportedLocaleValidator->isSupported($localeEn)->willReturn(true);
        $supportedLocaleValidator->isSupported($localeFr)->willReturn(true);

        $textChecker->check(['a_textarea' => 'Success'], $localeEn)->willReturn(['a_textarea' => $textCheckResultTextareaPrintEn]);
        $textCheckResultTextareaPrintEn->count()->willReturn(0);

        $textChecker->check(['a_textarea' => 'Fail'], $localeFr)->willThrow(new TextCheckFailedException());

        $expectedEvaluationResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelPrint, $localeEn, new Rate(100))
            ->addStatus($channelPrint, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelPrint, $localeEn, ['a_textarea' => 100])

            ->addStatus($channelPrint, $localeFr, CriterionEvaluationResultStatus::error())
        ;

        $this->evaluate($criterionEvaluation, $productValues)->shouldBeLike($expectedEvaluationResult);
    }

    public function it_does_not_evaluate_invalid_text(
        $textChecker,
        $localesByChannelQuery,
        $supportedLocaleValidator,
        $filterProductValuesForSpelling
    ) {
        $productId = new ProductId(1);
        $criterionEvaluation = new Write\CriterionEvaluation(
            new CriterionCode(EvaluateSpelling::CRITERION_CODE),
            $productId,
            CriterionEvaluationStatus::pending()
        );

        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US'],
        ]));

        $attributeText = $this->givenALocalizableAttributeOfTypeText('a_text');
        $attributeText2 = $this->givenALocalizableAttributeOfTypeText('a_second_text');
        $attributeText3 = $this->givenALocalizableAttributeOfTypeText('a_third_text');
        $attributeText4 = $this->givenALocalizableAttributeOfTypeText('a_fourth_text');
        $attributeText5 = $this->givenALocalizableAttributeOfTypeText('a_fifth_text');
        $attributeText6 = $this->givenALocalizableAttributeOfTypeText('a_sixth_text');
        $attributeText7 = $this->givenALocalizableAttributeOfTypeText('a_seventh_text');
        $attributeText8 = $this->givenALocalizableAttributeOfTypeText('a_eighth_text');
        $attributeTextarea = $this->givenALocalizableAttributeOfTypeTextarea('a_textarea');

        $textareaValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => 'CODE_1234',
            ],
        ], function ($value) { return $value; });

        $textValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => '1234_code',
            ],
        ], function ($value) { return $value; });

        $textValues2 = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => 'test@example.com',
            ],
        ], function ($value) { return $value; });

        $textValues3 = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => '123456789',
            ],
        ], function ($value) { return $value; });

        $textValues4 = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => '1234_4567',
            ],
        ], function ($value) { return $value; });

        $textValues5 = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => 'code-1234',
            ],
        ], function ($value) { return $value; });

        $textValues6 = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => '  CODE_1234  ',
            ],
        ], function ($value) { return $value; });

        $textValues7 = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => '',
            ],
        ], function ($value) { return $value; });

        $textValues8 = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => '   ',
            ],
        ], function ($value) { return $value; });

        $productTextValues = new ProductValues($attributeText, $textValues);
        $productTextValues2 = new ProductValues($attributeText2, $textValues2);
        $productTextValues3 = new ProductValues($attributeText3, $textValues3);
        $productTextValues4 = new ProductValues($attributeText4, $textValues4);
        $productTextValues5 = new ProductValues($attributeText5, $textValues5);
        $productTextValues6 = new ProductValues($attributeText6, $textValues6);
        $productTextValues7 = new ProductValues($attributeText7, $textValues7);
        $productTextValues8 = new ProductValues($attributeText8, $textValues8);
        $productTextareaValues = new ProductValues($attributeTextarea, $textareaValues);

        $productValues = (new ProductValuesCollection())
            ->add($productTextValues)
            ->add($productTextValues2)
            ->add($productTextValues3)
            ->add($productTextValues4)
            ->add($productTextValues5)
            ->add($productTextValues6)
            ->add($productTextValues7)
            ->add($productTextValues8)
            ->add($productTextareaValues);

        $filterProductValuesForSpelling->getFilteredProductValues($productValues)->willReturn([
            $productTextValues, $productTextValues2, $productTextValues3, $productTextValues4, $productTextValues5, $productTextValues6, $productTextValues7, $productTextValues8,
            $productTextareaValues
        ]);

        $channelEcommerce = new ChannelCode('ecommerce');
        $localeEn = new LocaleCode('en_US');

        $supportedLocaleValidator->isSupported($localeEn)->willReturn(true);

        $textChecker->check(Argument::cetera(), $localeEn)->shouldNotBeCalled();

        $expectedEvaluationResult = (new Write\CriterionEvaluationResult())
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::notApplicable())
        ;

        $this->evaluate($criterionEvaluation, $productValues)->shouldBeLike($expectedEvaluationResult);
    }

    public function it_does_not_evaluate_text_coming_from_word(
        MultipleTextsChecker $textChecker,
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        SupportedLocaleValidator $supportedLocaleValidator,
        FilterProductValuesForSpelling $filterProductValuesForSpelling
    ) {
        $productId = new ProductId(1);
        $criterionEvaluation = new Write\CriterionEvaluation(
            new CriterionCode(EvaluateSpelling::CRITERION_CODE),
            $productId,
            CriterionEvaluationStatus::pending()
        );

        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US'],
        ]));
        $attributeTextarea = $this->givenALocalizableAttributeOfTypeTextarea('a_textarea');

        $text = <<<TEXT
        <p><!--[if gte mso 9]><xml>\\n <o:OfficeDocumentSettings>\\n  <o:AllowPNG></o:AllowPNG>\\n </o:OfficeDocumentSettings>\\n</xml><![endif]--><!--[if gte mso 9]><xml>\\n <w:WordDocument>\\n  <w:View>Normal</w:View>\\n  <w:Zoom>0</w:Zoom>\\n  <w:TrackMoves></w:TrackMoves>\\n  <w:TrackFormatting></w:TrackFormatting>\\n  <![endif]--><strong>test</strong><strong>
        TEXT;
        $textareaValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => ['en_US' => $text],
        ], function ($value) { return $value; });

        $productTextareaValues = new ProductValues($attributeTextarea, $textareaValues);

        $productValues = (new ProductValuesCollection())->add($productTextareaValues);

        $filterProductValuesForSpelling->getFilteredProductValues($productValues)->willReturn([$productTextareaValues]);

        $channelEcommerce = new ChannelCode('ecommerce');
        $localeEn = new LocaleCode('en_US');
        $supportedLocaleValidator->isSupported($localeEn)->willReturn(true);

        $textChecker->check(Argument::cetera(), $localeEn)->shouldNotBeCalled();

        $expectedEvaluationResult = (new Write\CriterionEvaluationResult())
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::notApplicable());

        $this->evaluate($criterionEvaluation, $productValues)->shouldBeLike($expectedEvaluationResult);
    }

    private function givenALocalizableAttributeOfTypeText(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::text(), true);
    }

    private function givenALocalizableAttributeOfTypeTextarea(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::textarea(), true);
    }
}
