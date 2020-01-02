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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAttributeAsMainTitleValueFromProductIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAttributesByTypeFromProductQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetProductRawValuesByAttributeQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

class BuildProductValuesSpec extends ObjectBehavior
{
    public function let(
        GetProductRawValuesByAttributeQueryInterface $getProductRawValuesByAttributeQuery,
        GetAttributesByTypeFromProductQueryInterface $getAttributesByTypeFromProductQuery,
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        GetAttributeAsMainTitleValueFromProductIdInterface $getAttributeAsMainTitleValueFromProductId
    ) {
        $this->beConstructedWith($getProductRawValuesByAttributeQuery, $getAttributesByTypeFromProductQuery, $localesByChannelQuery, $getAttributeAsMainTitleValueFromProductId);
    }

    public function it_returns_nothing_when_there_is_no_attributes(
        $getAttributesByTypeFromProductQuery,
        $localesByChannelQuery,
        $getProductRawValuesByAttributeQuery
    ) {
        $localesByChannelQuery->execute()->willReturn([]);

        $getProductRawValuesByAttributeQuery
            ->execute(new ProductId(1), [])
            ->willReturn([]);

        $getAttributesByTypeFromProductQuery
            ->execute(new ProductId(1), AttributeTypes::TEXTAREA)
            ->willReturn([]);

        $this->buildTextareaValues(new ProductId(1))->shouldReturn([]);
    }

    public function it_returns_product_values_by_attributes_channel_and_locale(
        $getAttributesByTypeFromProductQuery,
        $localesByChannelQuery,
        $getProductRawValuesByAttributeQuery
    ) {
        $getAttributesByTypeFromProductQuery
            ->execute(new ProductId(1), AttributeTypes::TEXTAREA)
            ->willReturn(['textarea_1', 'textarea_2', 'textarea_3', 'textarea_4', 'textarea_5']);

        $localesByChannelQuery->execute()->willReturn([
            'ecommerce' => ['en_US', 'fr_FR'],
            'mobile' => ['en_US', 'fr_FR'],
        ]);

        $getProductRawValuesByAttributeQuery
            ->execute(new ProductId(1), ['textarea_1', 'textarea_2', 'textarea_3', 'textarea_4', 'textarea_5'])
            ->willReturn([
                'textarea_1' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'textarea1 text'
                    ],
                ],
                'textarea_2' => [
                    'ecommerce' => [
                        '<all_locales>' => 'textarea2 ecommerce'
                    ],
                    'mobile' => [
                        '<all_locales>' => 'textarea2 mobile'
                    ],
                ],
                'textarea_3' => [
                    '<all_channels>' => [
                        'en_US' => 'textarea3 en_US text',
                        'fr_FR' => 'textarea3 fr_FR text',
                    ],
                ],
                'textarea_4' => [
                    'ecommerce' => [
                        'en_US' => 'textarea4 ecommerce en_US text',
                        'fr_FR' => 'textarea4 ecommerce fr_FR text',
                    ],
                    'mobile' => [
                        'en_US' => 'textarea4 mobile en_US text',
                        'fr_FR' => 'textarea4 mobile fr_FR text',
                    ],
                ],
            ]);

        $this->buildTextareaValues(new ProductId(1))->shouldReturn([
            'textarea_1' => [
                'ecommerce' => [
                    'en_US' => 'textarea1 text',
                    'fr_FR' => 'textarea1 text',
                ],
                'mobile' => [
                    'en_US' => 'textarea1 text',
                    'fr_FR' => 'textarea1 text',
                ],
            ],
            'textarea_2' => [
                'ecommerce' => [
                    'en_US' => 'textarea2 ecommerce',
                    'fr_FR' => 'textarea2 ecommerce',
                ],
                'mobile' => [
                    'en_US' => 'textarea2 mobile',
                    'fr_FR' => 'textarea2 mobile',
                ],
            ],
            'textarea_3' => [
                'ecommerce' => [
                    'en_US' => 'textarea3 en_US text',
                    'fr_FR' => 'textarea3 fr_FR text',
                ],
                'mobile' => [
                    'en_US' => 'textarea3 en_US text',
                    'fr_FR' => 'textarea3 fr_FR text',
                ],
            ],
            'textarea_4' => [
                'ecommerce' => [
                    'en_US' => 'textarea4 ecommerce en_US text',
                    'fr_FR' => 'textarea4 ecommerce fr_FR text',
                ],
                'mobile' => [
                    'en_US' => 'textarea4 mobile en_US text',
                    'fr_FR' => 'textarea4 mobile fr_FR text',
                ],
            ],
            'textarea_5' => [
                'ecommerce' => [
                    'en_US' => null,
                    'fr_FR' => null,
                ],
                'mobile' => [
                    'en_US' => null,
                    'fr_FR' => null,
                ],
            ],
        ]);
    }

    public function it_returns_title_values_by_attributes_channel_and_locale(
        $localesByChannelQuery,
        $getAttributeAsMainTitleValueFromProductId
    ) {
        $localesByChannelQuery->execute()->willReturn([
            'ecommerce' => ['en_US', 'fr_FR'],
            'mobile' => ['en_US', 'fr_FR'],
        ]);

        $getAttributeAsMainTitleValueFromProductId->execute(new ProductId(1))->willReturn([
            'textarea_1' => [
                '<all_channels>' => [
                    '<all_locales>' => 'textarea1 text'
                ],
            ]
        ]);

        $this->buildTitleValues(new ProductId(1))->shouldReturn([
            'textarea_1' => [
                'ecommerce' => [
                    'en_US' => 'textarea1 text',
                    'fr_FR' => 'textarea1 text',
                ],
                'mobile' => [
                    'en_US' => 'textarea1 text',
                    'fr_FR' => 'textarea1 text',
                ],
            ]
        ]);
    }
}
