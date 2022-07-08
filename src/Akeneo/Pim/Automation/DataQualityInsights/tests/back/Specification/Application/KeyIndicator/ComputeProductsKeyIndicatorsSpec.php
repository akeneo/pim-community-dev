<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\ComputeProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductsKeyIndicatorsSpec extends ObjectBehavior
{
    public function let(
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery,
        ComputeProductsKeyIndicator       $goodEnrichment,
        ComputeProductsKeyIndicator       $hasImage
    )
    {
        $this->beConstructedWith($getLocalesByChannelQuery, [$goodEnrichment, $hasImage]);
    }

    public function it_computes_all_the_key_indicators_for_a_given_list_of_products(
        $getLocalesByChannelQuery,
        $goodEnrichment,
        $hasImage
    ) {
        $getLocalesByChannelQuery->getArray()->willReturn([
            'ecommerce' => ['en_US', 'fr_FR'],
            'mobile' => ['en_US'],
        ]);

        $productIds = ProductUuidCollection::fromStrings(['0932dfd0-5f9a-49fb-ad31-a990339406a2', '3370280b-6c76-4720-aac1-ae3f9613d555']);

        $expectedKeyIndicators = [
            '0932dfd0-5f9a-49fb-ad31-a990339406a2' => [
                'ecommerce' => [
                    'en_US' => [
                        'good_enrichment' => true,
                        'has_image' => true,
                    ],
                    'fr_FR' => [
                        'good_enrichment' => false,
                        'has_image' => null,
                    ],
                ],
                'mobile' => [
                    'en_US' => [
                        'good_enrichment' => null,
                        'has_image' => false,
                    ],
                ],
            ],
            '3370280b-6c76-4720-aac1-ae3f9613d555' => [
                'ecommerce' => [
                    'en_US' => [
                        'good_enrichment' => null,
                        'has_image' => null,
                    ],
                    'fr_FR' => [
                        'good_enrichment' => null,
                        'has_image' => null,
                    ],
                ],
                'mobile' => [
                    'en_US' => [
                        'good_enrichment' => null,
                        'has_image' => null,
                    ],
                ],
            ],
        ];

        $goodEnrichment->getCode()->willReturn(new KeyIndicatorCode('good_enrichment'));
        $hasImage->getCode()->willReturn(new KeyIndicatorCode('has_image'));

        $goodEnrichment->compute($productIds)->willReturn([
            '0932dfd0-5f9a-49fb-ad31-a990339406a2' => [
                'ecommerce' => [
                    'en_US' => true,
                    'fr_FR' => false,
                ],
            ],
            '3370280b-6c76-4720-aac1-ae3f9613d555' => [
                'ecommerce' => [
                    'en_US' => null,
                    'fr_FR' => null,
                ],
                'mobile' => [
                    'en_US' => null,
                ],
            ],
        ]);

        $hasImage->compute($productIds)->willReturn([
            '0932dfd0-5f9a-49fb-ad31-a990339406a2' => [
                'ecommerce' => [
                    'en_US' => true,
                ],
                'mobile' => [
                    'en_US' => false,
                ],
            ],
        ]);

        $this->compute($productIds)->shouldBeLike($expectedKeyIndicators);
    }
}
