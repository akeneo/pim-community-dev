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
use Akeneo\PerformanceAnalytics\Domain\ChannelCode;
use Akeneo\PerformanceAnalytics\Domain\FamilyCode;
use Akeneo\PerformanceAnalytics\Domain\LocaleCode;
use Akeneo\PerformanceAnalytics\Domain\Product\Product;
use Akeneo\PerformanceAnalytics\Domain\Product\ProductWasEnrichedMessage;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class ProductWasEnrichedMessageSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromProperties', [
            $this->getProduct(),
            ChannelCode::fromString('ecommerce'),
            LocaleCode::fromString('en_US'),
            new \DateTimeImmutable(),
            '1',
        ]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductWasEnrichedMessage::class);
    }

    public function it_normalizes()
    {
        $product = $this->getProduct();
        $this->beConstructedThrough('fromProperties', [
            $product,
            ChannelCode::fromString('ecommerce'),
            LocaleCode::fromString('en_US'),
            new \DateTimeImmutable('2022-01-05 00:00:00'),
            '1',
        ]);

        $this->normalize()->shouldReturn([
            'product_uuid' => $product->uuid()->toString(),
            'product_created_at' => $product->createdAt()->format('c'),
            'family_code' => $product->familyCode()?->toString(),
            'category_codes' => ['category1', 'category2'],
            'category_codes_with_ancestors' => ['category1', 'category2', 'parent'],
            'channel_code' => 'ecommerce',
            'locale_code' => 'en_US',
            'enriched_at' => '2022-01-05T00:00:00+00:00',
            'author_id' => '1',
        ]);
    }

    public function it_normalizes_without_family_code_and_category()
    {
        $product = Product::fromProperties(
            Uuid::uuid4(),
            new \DateTimeImmutable(),
            null,
            [],
            []
        );

        $this->beConstructedThrough('fromProperties', [
            $product,
            ChannelCode::fromString('ecommerce'),
            LocaleCode::fromString('en_US'),
            new \DateTimeImmutable('2022-01-05 00:00:00'),
            '1',
        ]);

        $this->normalize()->shouldReturn([
            'product_uuid' => $product->uuid()->toString(),
            'product_created_at' => $product->createdAt()->format('c'),
            'family_code' => null,
            'category_codes' => [],
            'category_codes_with_ancestors' => [],
            'channel_code' => 'ecommerce',
            'locale_code' => 'en_US',
            'enriched_at' => '2022-01-05T00:00:00+00:00',
            'author_id' => '1',
        ]);
    }

    private function getProduct(): Product
    {
        return Product::fromProperties(
            Uuid::uuid4(),
            new \DateTimeImmutable(),
            FamilyCode::fromString('family1'),
            [
                CategoryCode::fromString('category1'),
                CategoryCode::fromString('category2'),
            ],
            [
                CategoryCode::fromString('category1'),
                CategoryCode::fromString('category2'),
                CategoryCode::fromString('parent'),
            ]
        );
    }
}
