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
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\SupportedLocaleChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\TextChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductAttributesCodesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

class EvaluateSpellingSpec extends ObjectBehavior
{
    public function let(
        TextChecker $textChecker,
        BuildProductValuesInterface $buildProductValues,
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        SupportedLocaleChecker $supportedLocaleChecker,
        GetProductAttributesCodesInterface $getProductAttributesCodes
    ) {
        $this->beConstructedWith($textChecker, $buildProductValues, $localesByChannelQuery, $supportedLocaleChecker, $getProductAttributesCodes);
    }

    public function it_evaluates_rates_for_textarea_and_text_values(
        $textChecker,
        $buildProductValues,
        $localesByChannelQuery,
        $supportedLocaleChecker,
        $getProductAttributesCodes,
        TextCheckResultCollection $textCheckResultCollection1,
        TextCheckResultCollection $textCheckResultCollection2,
        TextCheckResultCollection $textCheckResultCollection3,
        TextCheckResultCollection $textCheckResultCollection4,
        TextCheckResultCollection $textCheckResultCollection5,
        TextCheckResultCollection $textCheckResultCollection6,
        TextCheckResultCollection $textCheckResultCollection7,
        TextCheckResultCollection $textCheckResultCollection8
    ) {
        $productId = new ProductId(1);
        $criterionEvaluation = new CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode(EvaluateSpelling::CRITERION_CODE),
            $productId,
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending()
        );

        $localesByChannelQuery->execute()->willReturn([
            'ecommerce' => ['en_US', 'fr_FR'],
            'print' => ['en_US', 'fr_FR'],
        ]);

        $getProductAttributesCodes->getLocalizableTextarea($productId)->willReturn(['textarea_1']);
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, ['textarea_1'])->willReturn([
            'textarea_1' => [
                'ecommerce' => [
                    'en_US' => '<p>Typos hapen. </p>',
                    'fr_FR' => '<p>Les fautes de frape arivent. </p>',
                    'it_IT' => '<p>I refusi accadono. </p>',
                ],
                'print' => [
                    'en_US' => '<p>Typos happen. </p>',
                    'fr_FR' => '<p>Les fautes de frappe arrivent. </p>',
                    'it_IT' => 'I refusi accadono.',
                ],
            ]
        ]);

        $getProductAttributesCodes->getLocalizableText($productId)->willReturn(['text_1']);
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, ['text_1'])->willReturn([
            'text_1' => [
                'ecommerce' => [
                    'en_US' => 'Typos happen.',
                    'fr_FR' => 'Les fautes de frape arivent',
                    'it_IT' => 'I refusi accadono.',
                ],
                'print' => [
                    'en_US' => 'Typos hapen.',
                    'fr_FR' => 'Les fautes de frappe arrivent',
                    'it_IT' => 'I refusi accadono.',
                ],
            ],
        ]);

        $supportedLocaleChecker->isSupported(new LocaleCode('en_US'))->willReturn(true);
        $supportedLocaleChecker->isSupported(new LocaleCode('fr_FR'))->willReturn(true);
        $supportedLocaleChecker->isSupported(new LocaleCode('it_IT'))->willReturn(false);

        $textCheckResultCollection1->count()->willReturn(1);
        $textCheckResultCollection2->count()->willReturn(1);
        $textCheckResultCollection3->count()->willReturn(0);
        $textCheckResultCollection4->count()->willReturn(0);
        $textCheckResultCollection5->count()->willReturn(0);
        $textCheckResultCollection6->count()->willReturn(1);
        $textCheckResultCollection7->count()->willReturn(1);
        $textCheckResultCollection8->count()->willReturn(0);

        $textChecker->check('<p>Typos hapen. </p>', 'en_US')->willReturn($textCheckResultCollection1);
        $textChecker->check('<p>Les fautes de frape arivent. </p>', 'fr_FR')->willReturn($textCheckResultCollection2);
        $textChecker->check('<p>Typos happen. </p>', 'en_US')->willReturn($textCheckResultCollection3);
        $textChecker->check('<p>Les fautes de frappe arrivent. </p>', 'fr_FR')->willReturn($textCheckResultCollection4);
        $textChecker->check('Typos happen.', 'en_US')->willReturn($textCheckResultCollection5);
        $textChecker->check('Les fautes de frape arivent', 'fr_FR')->willReturn($textCheckResultCollection6);
        $textChecker->check('Typos hapen.', 'en_US')->willReturn($textCheckResultCollection7);
        $textChecker->check('Les fautes de frappe arrivent', 'fr_FR')->willReturn($textCheckResultCollection8);


        $expectedRates = new CriterionRateCollection();
        $expectedRates
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(94))
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(82))
            ->addRate(new ChannelCode('print'), new LocaleCode('en_US'), new Rate(88))
            ->addRate(new ChannelCode('print'), new LocaleCode('fr_FR'), new Rate(100))
        ;

        $expectedData =  [
            'attributes' => [
                'ecommerce' => [
                    'en_US' => ['textarea_1'],
                    'fr_FR' => ['text_1', 'textarea_1'],
                ],
                'print' => [
                    'en_US' => ['text_1'],
                ],
            ],
        ];

        $expectedEvaluationResult = new CriterionEvaluationResult($expectedRates, $expectedData);

        $evaluation = $this->evaluate($criterionEvaluation);

        $evaluation->getData()->shouldBeLike($expectedEvaluationResult->getData());
        $evaluation->getRates()->shouldBeLike($expectedEvaluationResult->getRates());
    }
}
