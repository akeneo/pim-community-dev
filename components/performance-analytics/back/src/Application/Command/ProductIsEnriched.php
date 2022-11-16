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

namespace Akeneo\PerformanceAnalytics\Application\Command;

use Akeneo\PerformanceAnalytics\Domain\Product\ChannelLocale;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

class ProductIsEnriched
{
    /**
     * @param array<ChannelLocale> $channelsLocales
     */
    public function __construct(
        private UuidInterface $productUuid,
        private array $channelsLocales,
        private \DateTimeImmutable $enrichedAt,
    ) {
        Assert::allIsInstanceOf($this->channelsLocales, ChannelLocale::class);
    }

    public function productUuid(): UuidInterface
    {
        return $this->productUuid;
    }

    public function enrichedAt(): \DateTimeImmutable
    {
        return $this->enrichedAt;
    }

    /**
     * @return array<ChannelLocale>
     */
    public function channelsLocales(): array
    {
        return $this->channelsLocales;
    }
}
