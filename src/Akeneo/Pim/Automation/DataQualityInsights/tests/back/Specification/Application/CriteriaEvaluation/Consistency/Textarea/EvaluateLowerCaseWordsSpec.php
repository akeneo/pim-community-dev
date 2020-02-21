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

final class EvaluateLowerCaseWordsSpec extends ObjectBehavior
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
                'mobile' => ['en_US'],
            ]
        ));

        $productId = new ProductId(1);
        $getProductAttributesCodes->getTextarea($productId)->willReturn([]);
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, [])->willReturn([]);

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::notApplicable())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), CriterionEvaluationResultStatus::notApplicable())
            ->addStatus(new ChannelCode('mobile'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::notApplicable())
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
        $getProductAttributesCodes->getTextarea($productId)->willReturn(['textarea_1', 'textarea_2']);
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, ['textarea_1', 'textarea_2'])->willReturn([
            'textarea_1' => [
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
            ],
            'textarea_2' => [
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
            ],
        ]);

        $channelEcommerce = new ChannelCode('ecommerce');
        $channelMobile = new ChannelCode('mobile');
        $channelPrint = new ChannelCode('print');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelEcommerce, $localeEn, new Rate(28))
            ->addStatus($channelEcommerce, $localeEn, CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes($channelEcommerce, $localeEn, ['textarea_2'])

            ->addRate($channelEcommerce, $localeFr, new Rate(64))
            ->addStatus($channelEcommerce, $localeFr, CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes($channelEcommerce, $localeFr, ['textarea_2'])

            ->addRate($channelMobile, $localeEn, new Rate(40))
            ->addStatus($channelMobile, $localeEn, CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes($channelMobile, $localeEn, ['textarea_1', 'textarea_2'])

            ->addRate($channelMobile, $localeFr, new Rate(26))
            ->addStatus($channelMobile, $localeFr, CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes($channelMobile, $localeFr, ['textarea_1', 'textarea_2'])

            ->addRate($channelPrint, $localeFr, new Rate(100))
            ->addStatus($channelPrint, $localeFr, CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes($channelPrint, $localeFr, [])

            ->addStatus($channelPrint, $localeEn, CriterionEvaluationResultStatus::notApplicable())
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
