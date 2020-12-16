<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluableAttributesByProductQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductRawValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetEvaluableProductValuesQuerySpec extends ObjectBehavior
{
    public function let(
        GetProductRawValuesQueryInterface $getProductRawValuesQuery,
        GetEvaluableAttributesByProductQueryInterface $getEvaluableAttributesByProductQuery,
        GetLocalesByChannelQueryInterface $localesByChannelQuery
    ) {
        $this->beConstructedWith($getProductRawValuesQuery, $getEvaluableAttributesByProductQuery, $localesByChannelQuery);
    }

    public function it_returns_nothing_when_there_is_no_evaluable_attributes(
        GetEvaluableAttributesByProductQueryInterface $getEvaluableAttributesByProductQuery
    ) {
        $productId = new ProductId(42);

        $getEvaluableAttributesByProductQuery->execute($productId)->willReturn([]);
        $this->byProductId($productId)->shouldBeLike(new ProductValuesCollection());
    }

    public function it_returns_product_values_by_attributes_channel_and_locale(
        GetProductRawValuesQueryInterface $getProductRawValuesQuery,
        GetEvaluableAttributesByProductQueryInterface $getEvaluableAttributesByProductQuery,
        GetLocalesByChannelQueryInterface $localesByChannelQuery
    ) {
        $productId = new ProductId(42);

        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US', 'fr_FR'],
            'mobile' => ['en_US', 'fr_FR'],
        ]));

        $ecommerce = new ChannelCode('ecommerce');
        $mobile = new ChannelCode('mobile');
        $enUS = new LocaleCode('en_US');
        $frFR = new LocaleCode('fr_FR');

        $attributeText1 = new Attribute(new AttributeCode('a_text_not_scopable'), AttributeType::textarea(), true);
        $attributeText2 = new Attribute(new AttributeCode('a_text_not_localizable'), AttributeType::textarea(), false);
        $attributeTextarea1 = new Attribute(new AttributeCode('a_textarea'), AttributeType::textarea(), true);
        $attributeTextarea2 = new Attribute(new AttributeCode('a_textarea_without_values'), AttributeType::textarea(), true);

        $getEvaluableAttributesByProductQuery->execute($productId)->willReturn([
            $attributeText1, $attributeText2, $attributeTextarea1, $attributeTextarea2
        ]);

        $getProductRawValuesQuery
            ->execute($productId)
            ->willReturn([
                'a_text_not_localizable' => [
                    'ecommerce' => [
                        '<all_locales>' => 'A text not localizable for ecommerce'
                    ],
                    'mobile' => [
                        '<all_locales>' => 'A text not localizable for mobile'
                    ],
                ],
                'a_text_not_scopable' => [
                    '<all_channels>' => [
                        'en_US' => 'A text en_US',
                        'fr_FR' => 'A text fr_FR',
                    ],
                ],
                'a_textarea' => [
                    'ecommerce' => [
                        'en_US' => 'A textarea for ecommerce en_US',
                        'fr_FR' => 'A textarea for ecommerce fr_FR',
                    ],
                    'mobile' => [
                        'en_US' => 'A textarea for mobile en_US',
                        'fr_FR' => 'A textarea for mobile fr_FR',
                    ],
                ],
                'whatever' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'Whatever text'
                    ],
                ]
            ]);

        $expectedProductValues = (new ProductValuesCollection())
            ->add(new ProductValues($attributeText1, (new ChannelLocaleDataCollection())
                ->addToChannelAndLocale($ecommerce, $enUS, 'A text en_US')
                ->addToChannelAndLocale($ecommerce, $frFR, 'A text fr_FR')
                ->addToChannelAndLocale($mobile, $enUS, 'A text en_US')
                ->addToChannelAndLocale($mobile, $frFR, 'A text fr_FR')
            ))
            ->add(new ProductValues($attributeText2, (new ChannelLocaleDataCollection())
                ->addToChannelAndLocale($ecommerce, $enUS, 'A text not localizable for ecommerce')
                ->addToChannelAndLocale($ecommerce, $frFR, 'A text not localizable for ecommerce')
                ->addToChannelAndLocale($mobile, $enUS, 'A text not localizable for mobile')
                ->addToChannelAndLocale($mobile, $frFR, 'A text not localizable for mobile')
            ))
            ->add(new ProductValues($attributeTextarea1, (new ChannelLocaleDataCollection())
                ->addToChannelAndLocale($ecommerce, $enUS, 'A textarea for ecommerce en_US')
                ->addToChannelAndLocale($ecommerce, $frFR, 'A textarea for ecommerce fr_FR')
                ->addToChannelAndLocale($mobile, $enUS, 'A textarea for mobile en_US')
                ->addToChannelAndLocale($mobile, $frFR, 'A textarea for mobile fr_FR')
            ))
        ;

        $result = $this->byProductId($productId);

        $result->shouldBeLike($expectedProductValues);
    }
}
