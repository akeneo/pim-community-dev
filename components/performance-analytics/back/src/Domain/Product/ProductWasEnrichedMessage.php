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
use Akeneo\PerformanceAnalytics\Domain\ChannelCode;
use Akeneo\PerformanceAnalytics\Domain\LocaleCode;
use Akeneo\PerformanceAnalytics\Domain\Message;

final class ProductWasEnrichedMessage implements Message
{
    private function __construct(
        private Product $product,
        private ChannelCode $channelCode,
        private LocaleCode $localeCode,
        private \DateTimeImmutable $enrichedAt,
        private ?string $authorId,
    ) {
    }

    public static function fromProperties(
        Product $product,
        ChannelCode $channelCode,
        LocaleCode $localeCode,
        \DateTimeImmutable $enrichedAt,
        ?string $authorId,
    ): ProductWasEnrichedMessage {
        return new self(
            $product,
            $channelCode,
            $localeCode,
            $enrichedAt,
            $authorId
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
            'category_codes' => $this->normalizeCategoryCodes($this->product->categoryCodes()),
            'category_codes_with_ancestors' => $this->normalizeCategoryCodes($this->product->categoryWithAncestorsCodes()),
            'channel_code' => $this->channelCode->toString(),
            'locale_code' => $this->localeCode->toString(),
            'enriched_at' => $this->enrichedAt->format('c'),
            'author_id' => $this->authorId,
        ];
    }

    /**
     * @param CategoryCode[] $categoryCodes
     * @return string[]
     */
    private function normalizeCategoryCodes(array $categoryCodes): array
    {
        return \array_map(static fn (CategoryCode $category): string => $category->toString(), $categoryCodes);
    }
}
