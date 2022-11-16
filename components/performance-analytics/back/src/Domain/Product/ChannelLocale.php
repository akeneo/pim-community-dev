<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\PerformanceAnalytics\Domain\Product;

use Akeneo\PerformanceAnalytics\Domain\ChannelCode;
use Akeneo\PerformanceAnalytics\Domain\LocaleCode;

final class ChannelLocale
{
    private function __construct(
        private ChannelCode $channelCode,
        private LocaleCode $localeCode,
    ) {
    }

    public static function fromChannelAndLocaleString(
        string $channelCode,
        string $localeCode,
    ): ChannelLocale {
        return new ChannelLocale(
            ChannelCode::fromString($channelCode),
            LocaleCode::fromString($localeCode)
        );
    }

    /**
     * @return array<string, string>
     */
    public function normalize(): array
    {
        return [
            'channel_code' => $this->channelCode->toString(),
            'locale_code' => $this->localeCode->toString(),
        ];
    }
}
