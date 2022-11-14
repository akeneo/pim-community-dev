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

namespace Akeneo\PerformanceAnalytics\Domain\Product;

use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Webmozart\Assert\Assert;

final class ProductWasEnriched
{
    /**
     * @param array<ChannelLocale> $channelsLocales
     */
    private function __construct(
        private Product $product,
        private array $channelsLocales,
        private \DateTimeImmutable $enrichedAt
    ) {
        Assert::notEmpty($this->channelsLocales);
        Assert::allIsInstanceOf($this->channelsLocales, ChannelLocale::class);
    }

    /**
     * @param array<ChannelLocale> $channelsLocales
     */
    public static function fromProperties(
        Product $product,
        array $channelsLocales,
        \DateTimeImmutable $enrichedAt
    ): ProductWasEnriched {
        return new self(
            $product,
            $channelsLocales,
            $enrichedAt
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(): array
    {
        return [
            'product_uuid' => $this->product->uuid()->toString(),
            'product_created_at' => $this->product->createdAt()->format('c'),
            'family_code' => $this->product->familyCode()?->toString(),
            'category_codes' => array_map(fn (CategoryCode $category) => $category->toString(), $this->product->categories()),
            'channels_locales' => array_map(fn (ChannelLocale $channelLocale) => $channelLocale->normalize(), $this->channelsLocales),
            'enriched_at' => $this->enrichedAt->format('c'),
        ];
    }
}
