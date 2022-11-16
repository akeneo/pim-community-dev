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

use Akeneo\PerformanceAnalytics\Domain\ChannelCode;
use Akeneo\PerformanceAnalytics\Domain\LocaleCode;
use Ramsey\Uuid\UuidInterface;

class ProductIsEnriched
{
    public function __construct(
        private UuidInterface $productUuid,
        private ChannelCode $channelCode,
        private LocaleCode $localeCode,
        private \DateTimeImmutable $enrichedAt,
    ) {
    }

    public function productUuid(): UuidInterface
    {
        return $this->productUuid;
    }

    public function enrichedAt(): \DateTimeImmutable
    {
        return $this->enrichedAt;
    }

    public function channelCode(): ChannelCode
    {
        return $this->channelCode;
    }

    public function localeCode(): LocaleCode
    {
        return $this->localeCode;
    }
}
