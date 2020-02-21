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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Textarea;

use Akeneo\Pim\Automation\DataQualityInsights\Application\BuildProductValuesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductAttributesCodesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

final class EvaluateUppercaseWordsSpec extends ObjectBehavior
{
    public function let(
        BuildProductValuesInterface $buildProductValues,
        GetProductAttributesCodesInterface $getProductAttributesCodes,
        GetLocalesByChannelQueryInterface $localesByChannelQuery
    ) {
        $this->beConstructedWith($buildProductValues, $getProductAttributesCodes, $localesByChannelQuery);
    }

    public function it_sets_the_result_status_as_not_applicable_when_a_product_has_no_values_to_evaluate(
        BuildProductValuesInterface $buildProductValues,
        GetProductAttributesCodesInterface $getProductAttributesCodes,
        GetLocalesByChannelQueryInterface $localesByChannelQuery
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
                'mobile' => ['en_US', 'fr_FR'],
            ]
        ));

        $productId = new ProductId(1);
        $getProductAttributesCodes->getTextarea($productId)->willReturn([]);
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, [])->willReturn([]);

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::notApplicable())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), CriterionEvaluationResultStatus::notApplicable())
            ->addStatus(new ChannelCode('mobile'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::notApplicable())
            ->addStatus(new ChannelCode('mobile'), new LocaleCode('fr_FR'), CriterionEvaluationResultStatus::notApplicable())
        ;

        $this->evaluate(
            new Write\CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode('criterion1'),
                new ProductId(1),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending()
            )
        )->shouldBeLike($expectedResult);
    }

    public function it_evaluates_product_values(
        BuildProductValuesInterface $buildProductValues,
        GetProductAttributesCodesInterface $getProductAttributesCodes,
        GetLocalesByChannelQueryInterface $localesByChannelQuery
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
                'mobile' => ['en_US', 'fr_FR'],
                'print' => ['en_US', 'fr_FR'],
            ]
        ));

        $productId = new ProductId(1);
        $getProductAttributesCodes->getTextarea($productId)->willReturn(['textarea_1', 'textarea_2', 'textarea_3']);
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, ['textarea_1', 'textarea_2', 'textarea_3'])->willReturn([
            'textarea_1' => [
                'ecommerce' => [
                    'en_US' => '<p><br></p>',
                    'fr_FR' => 'Textarea1 text',
                ],
                'mobile' => [
                    'en_US' => 'TEXTAREA1 TEXT',
                    'fr_FR' => 'TEXTAREA1 TEXT',
                ],
                'print' => [
                    'en_US' => null,
                    'fr_FR' => null,
                ],
            ],
            'textarea_2' => [
                'ecommerce' => [
                    'en_US' => '<strong>textarea2 ecommerce éèâö</strong>',
                    'fr_FR' => '<strong>TEXTAREA2 ECOMMERCE ÉÈÂÖ</strong>',
                ],
                'mobile' => [
                    'en_US' => '<STRONG>TEXTAREA2 MOBILE EN_US</STRONG>',
                    'fr_FR' => '<STRONG>Textarea2 mobile fr_fr</STRONG>',
                ],
                'print' => [
                    'en_US' => null,
                    'fr_FR' => 'text',
                ],
            ],
            'textarea_3' => [
                'ecommerce' => [
                    'en_US' => '123456',
                    'fr_FR' => '123 456',
                ],
                'mobile' => [
                    'en_US' => '12.34',
                    'fr_FR' => '12-23',
                ],
                'print' => [
                    'en_US' => '12_34',
                    'fr_FR' => '1234!!',
                ],
            ],
        ]);

        $channelEcommerce = new ChannelCode('ecommerce');
        $channelMobile = new ChannelCode('mobile');
        $channelPrint = new ChannelCode('print');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelEcommerce, $localeEn, new Rate(100))
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes($channelEcommerce, $localeEn, [])

            ->addRate($channelEcommerce, $localeFr, new Rate(67))
            ->addStatus($channelEcommerce, $localeFr, CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes($channelEcommerce, $localeFr, ['textarea_2'])

            ->addRate($channelMobile, $localeEn, new Rate(33))
            ->addStatus($channelMobile, $localeEn, CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes($channelMobile, $localeEn, ['textarea_1', 'textarea_2'])

            ->addRate($channelMobile, $localeFr, new Rate(67))
            ->addStatus($channelMobile, $localeFr, CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes($channelMobile, $localeFr, ['textarea_1'])

            ->addRate($channelPrint, $localeEn, new Rate(100))
            ->addStatus($channelPrint, $localeEn, CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes($channelPrint, $localeEn, [])

            ->addRate($channelPrint, $localeFr, new Rate(100))
            ->addStatus($channelPrint, $localeFr, CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes($channelPrint, $localeFr, [])
        ;

        $this->evaluate(
            new Write\CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode('criterion1'),
                new ProductId(1),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending()
            )
        )->shouldBeLike($expectedResult);
    }
}
