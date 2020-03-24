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


namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Application\BuildProductValuesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\EvaluateSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\TextChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\TextCheckFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetTextareaAttributeCodesCompatibleWithSpellingQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetTextAttributeCodesCompatibleWithSpellingQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
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
        TextChecker $textChecker,
        BuildProductValuesInterface $buildProductValues,
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        SupportedLocaleValidator $supportedLocaleValidator,
        GetTextAttributeCodesCompatibleWithSpellingQueryInterface $getTextAttributeCodesCompatibleWithSpellingQuery,
        GetTextareaAttributeCodesCompatibleWithSpellingQueryInterface $getTextareaAttributeCodesCompatibleWithSpellingQuery,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($textChecker, $buildProductValues, $localesByChannelQuery, $supportedLocaleValidator, $getTextAttributeCodesCompatibleWithSpellingQuery, $getTextareaAttributeCodesCompatibleWithSpellingQuery, $logger);
    }

    public function it_evaluates_rates_for_textarea_and_text_values(
        $textChecker,
        $buildProductValues,
        $localesByChannelQuery,
        $supportedLocaleValidator,
        $getTextAttributeCodesCompatibleWithSpellingQuery,
        $getTextareaAttributeCodesCompatibleWithSpellingQuery,
        $logger,
        TextCheckResultCollection $textCheckResultTextareaEcommerceEn,
        TextCheckResultCollection $textCheckResultTextareaPrintEn,
        TextCheckResultCollection $textCheckResultTextareaEcommerceFr,
        TextCheckResultCollection $textCheckResultTextEcommerceEn,
        TextCheckResultCollection $textCheckResultTextEcommerceFR,
        TextCheckResultCollection $textCheckResultTextPrintEn
    ) {
        $productId = new ProductId(1);
        $criterionEvaluation = new Write\CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode(EvaluateSpelling::CRITERION_CODE),
            $productId,
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending()
        );

        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US', 'fr_FR', 'it_IT'],
            'print' => ['en_US', 'fr_FR'],
        ]));

        $getTextareaAttributeCodesCompatibleWithSpellingQuery->byProductId($productId)->willReturn(['textarea_1']);
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, ['textarea_1'])->willReturn([
            'textarea_1' => [
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
            ]
        ]);

        $getTextAttributeCodesCompatibleWithSpellingQuery->byProductId($productId)->willReturn(['text_1']);
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, ['text_1'])->willReturn([
            'text_1' => [
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
            ],
        ]);

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

        $logger->info(Argument::cetera())->shouldBeCalledTimes(6);

        $textChecker->check('<p>Typos hapen. </p>', $localeEn)->willReturn($textCheckResultTextareaEcommerceEn);
        $textChecker->check('<p>Typos happen. </p>', $localeEn)->willReturn($textCheckResultTextareaPrintEn);
        $textChecker->check('<p>Les fautes de frappe arrivent. </p>', $localeFr)->willReturn($textCheckResultTextareaEcommerceFr);
        $textChecker->check('Typos happen.', $localeEn)->willReturn($textCheckResultTextEcommerceEn);
        $textChecker->check('Les fautes de frappe arrivent', $localeFr)->willReturn($textCheckResultTextEcommerceFR);
        $textChecker->check('Typos hapen.', $localeEn)->willReturn($textCheckResultTextPrintEn);

        $expectedEvaluationResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelEcommerce, $localeEn, new Rate(94))
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes($channelEcommerce, $localeEn, ['textarea_1'])

            ->addRate($channelEcommerce, $localeFr, new Rate(100))
            ->addStatus($channelEcommerce, $localeFr, CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes($channelEcommerce, $localeFr, [])

            ->addStatus($channelEcommerce, $localeIt, CriterionEvaluationResultStatus::notApplicable())

            ->addRate($channelPrint, $localeEn, new Rate(88))
            ->addStatus($channelPrint, $localeEn, CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes($channelPrint, $localeEn, ['text_1'])

            ->addStatus($channelPrint, $localeFr, CriterionEvaluationResultStatus::notApplicable())
        ;

        $this->evaluate($criterionEvaluation)->shouldBeLike($expectedEvaluationResult);
    }

    public function it_sets_status_in_error_when_the_text_checking_fails(
        TextChecker $textChecker,
        BuildProductValuesInterface $buildProductValues,
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        SupportedLocaleValidator $supportedLocaleValidator,
        GetTextAttributeCodesCompatibleWithSpellingQueryInterface $getTextAttributeCodesCompatibleWithSpellingQuery,
        GetTextareaAttributeCodesCompatibleWithSpellingQueryInterface $getTextareaAttributeCodesCompatibleWithSpellingQuery,
        TextCheckResultCollection $textCheckResultTextareaPrintEn,
        $logger
    ) {
        $productId = new ProductId(42);
        $criterionEvaluation = new Write\CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode(EvaluateSpelling::CRITERION_CODE),
            $productId,
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending()
        );

        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'print' => ['en_US', 'fr_FR'],
        ]));

        $getTextAttributeCodesCompatibleWithSpellingQuery->byProductId($productId)->willReturn([]);
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, [])->willReturn([]);

        $getTextareaAttributeCodesCompatibleWithSpellingQuery->byProductId($productId)->willReturn(['textarea_1']);
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, ['textarea_1'])->willReturn([
            'textarea_1' => [
                'print' => [
                    'en_US' => 'Success',
                    'fr_FR' => 'Fail',
                ],
            ]
        ]);

        $channelPrint = new ChannelCode('print');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $supportedLocaleValidator->isSupported($localeEn)->willReturn(true);
        $supportedLocaleValidator->isSupported($localeFr)->willReturn(true);

        $logger->error(Argument::cetera())->shouldBeCalled();
        $logger->info(Argument::cetera())->shouldBeCalledTimes(2);

        $textChecker->check('Success', $localeEn)->willReturn($textCheckResultTextareaPrintEn);
        $textCheckResultTextareaPrintEn->count()->willReturn(0);

        $textChecker->check('Fail', $localeFr)->willThrow(new TextCheckFailedException());

        $expectedEvaluationResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelPrint, $localeEn, new Rate(100))
            ->addStatus($channelPrint, $localeEn, CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes($channelPrint, $localeEn, [])

            ->addStatus($channelPrint, $localeFr, CriterionEvaluationResultStatus::error())
        ;

        $this->evaluate($criterionEvaluation)->shouldBeLike($expectedEvaluationResult);
    }
}
