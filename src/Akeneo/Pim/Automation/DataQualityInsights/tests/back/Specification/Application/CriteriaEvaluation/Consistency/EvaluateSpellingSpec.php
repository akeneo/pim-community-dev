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

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\EvaluateSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\FilterProductValuesForSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\TextChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\TextCheckFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
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
        TextChecker $textChecker,
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

        $filterProductValuesForSpelling->getTextValues($productValues)->willReturn(new \ArrayIterator([$productTextValues]));
        $filterProductValuesForSpelling->getTextareaValues($productValues)->willReturn(new \ArrayIterator([$productTextareaValues]));

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
        TextChecker $textChecker,
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        SupportedLocaleValidator $supportedLocaleValidator,
        TextCheckResultCollection $textCheckResultTextareaPrintEn,
        FilterProductValuesForSpelling $filterProductValuesForSpelling,
        $logger
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

        $filterProductValuesForSpelling->getTextValues($productValues)->willReturn(new \ArrayIterator([$productTextValues]));
        $filterProductValuesForSpelling->getTextareaValues($productValues)->willReturn(new \ArrayIterator([]));

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
            ->addRateByAttributes($channelPrint, $localeEn, ['a_textarea' => 100])

            ->addStatus($channelPrint, $localeFr, CriterionEvaluationResultStatus::error())
        ;

        $this->evaluate($criterionEvaluation, $productValues)->shouldBeLike($expectedEvaluationResult);
    }

    private function givenALocalizableAttributeOfTypeText(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::text(), true, false);
    }

    private function givenALocalizableAttributeOfTypeTextarea(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::textarea(), true, false);
    }
}
