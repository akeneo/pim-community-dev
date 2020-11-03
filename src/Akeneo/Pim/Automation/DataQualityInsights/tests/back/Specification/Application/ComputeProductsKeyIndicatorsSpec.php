<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\ComputeProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductsKeyIndicatorsSpec extends ObjectBehavior
{
    public function let(
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery,
        ComputeProductsKeyIndicator $goodEnrichment,
        ComputeProductsKeyIndicator $hasImage
    ) {
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

        $productIds = [new ProductId(13), new ProductId(42)];

        $expectedKeyIndicators = [
            13 => [
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
            42 => [
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

        $goodEnrichment->getName()->willReturn('good_enrichment');
        $hasImage->getName()->willReturn('has_image');

        $goodEnrichment->compute($productIds)->willReturn([
            13 => [
                'ecommerce' => [
                    'en_US' => true,
                    'fr_FR' => false,
                ],
            ],
            42 => [
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
            13 => [
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
