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

use Webmozart\Assert\Assert;

final class ChannelLocale
{
    private function __construct(
        private string $channelCode,
        private string $localeCode,
    ) {
        Assert::stringNotEmpty($this->channelCode);
        Assert::stringNotEmpty($this->localeCode);
    }

    public static function fromChannelAndLocale(
        string $channelCode,
        string $localeCode,
    ): ChannelLocale {
        return new ChannelLocale($channelCode, $localeCode);
    }

    /**
     * @return array<string, string>
     */
    public function normalize(): array
    {
        return [
            'channel_code' => $this->channelCode,
            'locale_code' => $this->localeCode,
        ];
    }
}
