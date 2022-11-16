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
use Akeneo\PerformanceAnalytics\Domain\Product\ProductWasEnriched;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class ProductWasEnrichedSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromProperties', [
            $this->getProduct(),
            [
                ChannelLocale::fromChannelAndLocaleString('e-commerce', 'fr_FR'),
                ChannelLocale::fromChannelAndLocaleString('e-commerce', 'en_GB'),
            ],
            new \DateTimeImmutable(),
        ]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductWasEnriched::class);
    }

    public function it_can_not_be_created_without_channel_locale()
    {
        $this->beConstructedThrough('fromProperties', [
            $this->getProduct(),
            [],
            new \DateTimeImmutable(),
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_can_not_be_created_with_invalid_channel_locale()
    {
        $this->beConstructedThrough('fromProperties', [
            $this->getProduct(),
            [
                'channel_locale1',
                'channel_locale2',
            ],
            new \DateTimeImmutable(),
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_normalizes()
    {
        $product = $this->getProduct();
        $this->beConstructedThrough('fromProperties', [
            $product,
            [
                ChannelLocale::fromChannelAndLocaleString('e-commerce', 'fr_FR'),
                ChannelLocale::fromChannelAndLocaleString('e-commerce', 'en_GB'),
            ],
            new \DateTimeImmutable('2022-01-05 00:00:00'),
        ]);

        $this->normalize()->shouldReturn([
            'product_uuid' => $product->uuid()->toString(),
            'product_created_at' => $product->createdAt()->format('c'),
            'family_code' => $product->familyCode()?->toString(),
            'category_codes' => array_map(fn (CategoryCode $category) => $category->toString(), $product->categories()),
            'channels_locales' => [
                ['channel_code' => 'e-commerce', 'locale_code' => 'fr_FR'],
                ['channel_code' => 'e-commerce', 'locale_code' => 'en_GB'],
            ],
            'enriched_at' => '2022-01-05T00:00:00+00:00',
        ]);
    }

    public function it_normalizes_without_family_code_and_category()
    {
        $product = Product::fromProperties(
            Uuid::uuid4(),
            new \DateTimeImmutable(),
            null,
            []
        );

        $this->beConstructedThrough('fromProperties', [
            $product,
            [
                ChannelLocale::fromChannelAndLocaleString('e-commerce', 'fr_FR'),
                ChannelLocale::fromChannelAndLocaleString('e-commerce', 'en_GB'),
            ],
            new \DateTimeImmutable('2022-01-05 00:00:00'),
        ]);

        $this->normalize()->shouldReturn([
            'product_uuid' => $product->uuid()->toString(),
            'product_created_at' => $product->createdAt()->format('c'),
            'family_code' => null,
            'category_codes' => [],
            'channels_locales' => [
                ['channel_code' => 'e-commerce', 'locale_code' => 'fr_FR'],
                ['channel_code' => 'e-commerce', 'locale_code' => 'en_GB'],
            ],
            'enriched_at' => '2022-01-05T00:00:00+00:00',
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
            ]
        );
    }
}
