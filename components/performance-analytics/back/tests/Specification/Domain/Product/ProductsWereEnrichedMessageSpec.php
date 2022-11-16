<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\PerformanceAnalytics\Domain\Product;

use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Akeneo\PerformanceAnalytics\Domain\FamilyCode;
use Akeneo\PerformanceAnalytics\Domain\Product\ChannelLocale;
use Akeneo\PerformanceAnalytics\Domain\Product\Product;
use Akeneo\PerformanceAnalytics\Domain\Product\ProductsWereEnrichedMessage;
use Akeneo\PerformanceAnalytics\Domain\Product\ProductWasEnriched;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class ProductsWereEnrichedMessageSpec extends ObjectBehavior
{
    public function let()
    {
        $product1 = Product::fromProperties(
            Uuid::uuid4(),
            new \DateTimeImmutable(),
            FamilyCode::fromString('family1'),
            [
                CategoryCode::fromString('category1'),
                CategoryCode::fromString('category2'),
            ]
        );

        $product2 = Product::fromProperties(
            Uuid::uuid4(),
            new \DateTimeImmutable(),
            FamilyCode::fromString('family2'),
            [
                CategoryCode::fromString('category2'),
                CategoryCode::fromString('category3'),
            ]
        );

        $this->beConstructedThrough('fromCollection', [
            [
                ProductWasEnriched::fromProperties(
                    $product1,
                    [
                        ChannelLocale::fromChannelAndLocale('e-commerce', 'fr_FR'),
                        ChannelLocale::fromChannelAndLocale('e-commerce', 'en_GB'),
                    ],
                    new \DateTimeImmutable()
                ),
                ProductWasEnriched::fromProperties(
                    $product2,
                    [
                        ChannelLocale::fromChannelAndLocale('e-commerce', 'fr_FR'),
                        ChannelLocale::fromChannelAndLocale('mobile', 'fr_FR'),
                    ],
                    new \DateTimeImmutable()
                ),
            ],
        ]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductsWereEnrichedMessage::class);
    }

    public function it_can_not_create_with_invalid_products_was_enriched_collection()
    {
        $this->beConstructedThrough('fromCollection', [['product1']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_normalizes_products_were_enriched()
    {
        $this->normalize()->shouldHaveCount(2);
    }
}
